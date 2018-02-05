<?php

namespace Drupal\customer_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\customer_entity\Entity\CustomerEntityInterface;

/**
 * Class CustomerEntityController.
 *
 *  Returns responses for Customer routes.
 */
class CustomerEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Customer  revision.
   *
   * @param int $customer_entity_revision
   *   The Customer  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($customer_entity_revision) {
    $customer_entity = $this->entityManager()->getStorage('customer_entity')->loadRevision($customer_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('customer_entity');

    return $view_builder->view($customer_entity);
  }

  /**
   * Page title callback for a Customer  revision.
   *
   * @param int $customer_entity_revision
   *   The Customer  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($customer_entity_revision) {
    $customer_entity = $this->entityManager()->getStorage('customer_entity')->loadRevision($customer_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $customer_entity->label(), '%date' => format_date($customer_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Customer .
   *
   * @param \Drupal\customer_entity\Entity\CustomerEntityInterface $customer_entity
   *   A Customer  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CustomerEntityInterface $customer_entity) {
    $account = $this->currentUser();
    $langcode = $customer_entity->language()->getId();
    $langname = $customer_entity->language()->getName();
    $languages = $customer_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $customer_entity_storage = $this->entityManager()->getStorage('customer_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $customer_entity->label()]) : $this->t('Revisions for %title', ['%title' => $customer_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all customer revisions") || $account->hasPermission('administer customer entities')));
    $delete_permission = (($account->hasPermission("delete all customer revisions") || $account->hasPermission('administer customer entities')));

    $rows = [];

    $vids = $customer_entity_storage->revisionIds($customer_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\customer_entity\CustomerEntityInterface $revision */
      $revision = $customer_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $customer_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.customer_entity.revision', ['customer_entity' => $customer_entity->id(), 'customer_entity_revision' => $vid]));
        }
        else {
          $link = $customer_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.customer_entity.translation_revert',
                [
                  'customer_entity' => $customer_entity->id(),
                  'customer_entity_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
              Url::fromRoute('entity.customer_entity.revision_revert',
                [
                  'customer_entity' => $customer_entity->id(),
                  'customer_entity_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.customer_entity.revision_delete', ['customer_entity' => $customer_entity->id(), 'customer_entity_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['customer_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
