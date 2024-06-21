<?php

namespace Drupal\ovh_api_rest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Domain Ovh Endpoint entities.
 *
 * @ingroup ovh_api_rest
 */
class DomainOvhEntityListBuilder extends EntityListBuilder {
  
  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Domain Ovh Endpoint ID');
    $header['name'] = $this->t('Name');
    $header['domain_id_drupal'] = 'domain_id_drupal';
    $header['type_site'] = $this->t('Type site');
    $header['user_id'] = $this->t('Author');
    return $header + parent::buildHeader();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute($entity->label(), 'entity.domain_ovh_entity.edit_form', [
      'domain_ovh_entity' => $entity->id()
    ]);
    $domainId = $entity->getDomainIdDrupal();
    $data = [
      'data' => []
    ];
    $data['data'][] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $domainId
    ];
    $domain = \Drupal\domain\Entity\Domain::load($domainId);
    if ($domain) {
      $uri = $domain->getScheme() . $domain->getHostname();
      $data['data'][] = [
        '#type' => 'link',
        '#title' => $domain->label(),
        '#url' => \Drupal\Core\Url::fromUri($uri),
        '#options' => [
          'attributes' => [
            'target' => '_blank'
          ]
        ]
      ];
    }
    $row['domain_id_drupal'] = $data;
    $row['type_site'] = $entity->getTypeSite();
    $row['user_id'] = $entity->getOwner()->getDisplayName();
    return $row + parent::buildRow($entity);
  }
  
  public function render() {
    $build = parent::render();
    $build['form_filter'] = \Drupal::formBuilder()->getForm('\Drupal\wb_optimisation\Form\FilterForm');
    $build['form_filter']['#weight'] = -10;
    return $build;
  }
  
  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array An array of entity IDs.
   */
  protected function getEntityIds() {
    $request = $this->getRequest();
    $contain = $request->query->get("contain");
    $limit = !empty($request->query->get("limit")) ? $request->query->get("limit") : $this->limit;
    $type_site = !empty($request->query->get("type_site")) ? $request->query->get("type_site") : null;
    /**
     *
     * @var \Drupal\Core\Entity\Query\Sql\Query $query
     */
    $query = $this->getStorage()->getQuery()->accessCheck(TRUE)->sort($this->entityType->getKey('id'));
    if (!empty($contain))
      $query->condition("domain_id_drupal", "%$contain%", "LIKE");
    // --
    if ($type_site && $type_site != 'all')
      if ($type_site == 'null')
        $query->condition('type_site', NULL, 'IS NULL');
      else {
        $query->condition('type_site', $type_site, '=');
      }
    
    // Only add the pager if a limit is specified.
    if ($limit) {
      $query->pager($limit);
    }
    return $query->execute();
  }
  
  /**
   * Gets the request object.
   *
   * @return \Symfony\Component\HttpFoundation\Request The request object.
   */
  protected function getRequest() {
    if (!$this->requestStack) {
      $this->requestStack = \Drupal::service('request_stack');
    }
    return $this->requestStack->getCurrentRequest();
  }
  
}
