<?php

namespace Drupal\ovh_api_rest\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Messenger\MessengerInterface;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Ovh\Api;
use Drupal\ovh_api_rest\Entity\DomainBuy;

class ManageBuyDomain {
  /**
   *
   * @var \Drupal\ovh_api_rest\Entity\DomainBuy
   */
  protected $domain_buy;
  /**
   *
   * @var Api
   */
  protected $OVH;
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $user;
  
  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   *
   * @var array
   */
  protected $config = [];
  /**
   *
   * @var array
   */
  protected $form_submit = [];
  
  /**
   * liste des durées
   *
   * @var array
   */
  protected array $durations = [];
  
  /**
   * id du panier;
   *
   * @var string
   */
  protected $cartId;
  
  function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $user, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
    $this->messenger = $messenger;
  }
  
  //
  private function initApi() {
    if (!$this->OVH) {
      $conf = ConfigDrupal::config('ovh_api_rest.settings');
      $this->config = $conf;
      $application_key = $conf['api_key'];
      $application_secret = $conf['api_secret'];
      $api_endpoint = 'ovh-eu';
      $consumer_key = $conf['consumer_key'];
      $this->OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
    }
  }
  
  public function searchDomain($search, array $form_submit) {
    $this->form_submit = $form_submit;
    $this->initApi();
    $this->initCartOvh();
    return $this->getInformation();
  }
  
  public function getPeriodesOptions() {
    $options = [];
    foreach ($this->durations as $value) {
      switch ($value) {
        case 'P1Y':
          $options['P1Y'] = '1 an';
          break;
        case 'P2Y':
          $options['P2Y'] = '2 ans';
          break;
        case 'P3Y':
          $options['P3Y'] = '3 ans';
          break;
        case 'P4Y':
          $options['P4Y'] = '4 ans';
          break;
        case 'P5Y':
          $options['P5Y'] = '5 an';
          break;
      }
    }
    return $options;
  }
  
  public function saveDomain($domain) {
    $this->saveAttributes('domaine', $domain);
  }
  
  public function savePeriode($periode) {
    $this->saveAttributes('periode', $periode);
  }
  
  public function getDomain() {
    if ($this->domain_buy)
      return $this->domain_buy->get('domaine')->value;
  }
  
  public function getPeriode() {
    if ($this->domain_buy)
      return $this->domain_buy->get('periode')->value;
  }
  
  public function getTypePack() {
    if ($this->domain_buy)
      return $this->domain_buy->get('type_pack')->value;
  }
  
  /**
   *
   * @param string $key
   * @param string $value
   */
  protected function saveAttributes($key, $value) {
    $this->domain_buy->set($key, $value);
    $this->domain_buy->save();
  }
  
  /**
   * On initialise le panier.
   */
  protected function initCartOvh() {
    $session = \Drupal::request()->getSession();
    if ($session->has('ovh_cart_id')) {
      $domain_buys = $this->entityTypeManager->getStorage('domain_buy')->loadByProperties([
        'cart_id' => $session->get('ovh_cart_id')
      ]);
      
      if (!empty($domain_buys)) {
        $this->domain_buy = reset($domain_buys);
        if ($this->checkDate()) {
          $this->initCart();
        }
      }
      else
        throw new \Exception(' Error to load entity with cart_id : ' . $session->get('ovh_cart_id'));
    }
    else {
      $this->initCart();
    }
    $this->cartId = $this->domain_buy->getCartId();
  }
  
  /**
   *
   * @throws \Exception
   */
  protected function initCart() {
    $session = \Drupal::request()->getSession();
    $body = [
      'ovhSubsidiary' => "FR"
    ];
    $result = $this->OVH->post("/order/cart", $body);
    if ($result['cartId']) {
      $values = [
        'name' => $this->form_submit['domaine'],
        'cart_id' => $result['cartId']
      ];
      $this->domain_buy = DomainBuy::create($values);
      $this->domain_buy->save();
      $session->set('ovh_cart_id', $result['cartId']);
    }
    else
      throw new \Exception('error init cart_id');
  }
  
  /**
   * --
   */
  protected function checkDate() {
    $date = new \DateTime();
    $date->setTimestamp($this->domain_buy->get('created')->value);
    $today = new \DateTime();
    $interval = $today->diff($date);
    return $interval->days;
  }
  
  /**
   * On verifie si on peut effectivement acheter le domaine
   */
  protected function getInformation() {
    if (empty($this->domain_buy)) {
      $this->initCartOvh();
    }
    
    $body = [
      'domain' => $this->form_submit['domaine']
    ];
    $result = $this->OVH->get("/order/cart/$this->cartId/domain", $body);
    // \Stephane888\Debug\debugLog::kintDebugDrupal($result,
    // 'getInformation-ovh', true);
    if (!empty($result[0])) {
      $result = $result[0];
      if ($result['action'] == 'create') {
        $this->domain_buy->set('price', $this->getTotalPrice($result['prices']));
        $this->domain_buy->set('offer_id', $result['offerId']);
        $this->domain_buy->set('pricing_mode', $result['pricingMode']);
        $this->domain_buy->save();
        $this->durations = $result['duration'];
        $result['status_domain'] = true;
        return $result;
      }
      else
        throw new \Exception('nom de domaine non disponible');
    }
    else
      return [
        'status_domain' => false
      ];
  }
  
  /**
   */
  protected function addDomainInCart() {
    if (!empty($this->domain_buy)) {
      $body = [
        'domain' => $this->form_submit['domaine'],
        'duration' => $this->form_submit['periode'],
        'offerId' => $this->domain_buy->get('offer_id')->value // @depreciate
      ];
      $result = $this->OVH->post("/order/cart/$this->cartId/domain", $body);
      if (!empty($result['itemId'])) {
        $this->domain_buy->set('item_id', $result['itemId']);
        $this->domain_buy->save();
      }
    }
  }
  
  protected function getConfigurations() {
    if (!empty($this->domain_buy)) {
      $itemId = $this->domain_buy->get('item_id')->value;
      $result = $this->OVH->get("/order/cart/$this->cartId/item/$itemId/requiredConfiguration");
      // on n'y reviendra.
    }
  }
  
  /**
   * --
   */
  protected function Dry_payOnOvh() {
    $result = $this->OVH->get("/order/cart/$this->cartId/domain");
  }
  
  protected function payOnOvh() {
    if (!empty($this->domain_buy)) {
      $body = [
        'autoPayWithPreferredPaymentMethod' => true,
        'waiveRetractationPeriod' => true
      ];
      $result = $this->OVH->post("/order/cart/$this->cartId/domain", $body);
    }
  }
  
  protected function getTotalPrice($prices) {
    foreach ($prices as $value) {
      if ($value['label'] == 'TOTAL') {
        return $value['price']['value'];
      }
    }
    throw new \exception("impossible d'obtenir le total du prix");
  }
  
}