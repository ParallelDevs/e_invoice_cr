<?php

namespace Drupal\tax_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\tax_entity\Entity\TaxEntityInterface;

/**
 * Class TaxEntityController.
 *
 *  Returns responses for Tax entity routes.
 */
class TaxEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Tax entity  revision.
   *
   * @param int $tax_entity_revision
   *   The Tax entity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($tax_entity_revision) {
    $tax_entity = $this->entityManager()->getStorage('tax_entity')->loadRevision($tax_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('tax_entity');

    return $view_builder->view($tax_entity);
  }

  /**
   * Page title callback for a Tax entity  revision.
   *
   * @param int $tax_entity_revision
   *   The Tax entity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($tax_entity_revision) {
    $tax_entity = $this->entityManager()->getStorage('tax_entity')->loadRevision($tax_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $tax_entity->label(), '%date' => format_date($tax_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Tax entity .
   *
   * @param \Drupal\tax_entity\Entity\TaxEntityInterface $tax_entity
   *   A Tax entity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TaxEntityInterface $tax_entity) {
    $account = $this->currentUser();
    $langcode = $tax_entity->language()->getId();
    $langname = $tax_entity->language()->getName();
    $languages = $tax_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $tax_entity_storage = $this->entityManager()->getStorage('tax_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $tax_entity->label()]) : $this->t('Revisions for %title', ['%title' => $tax_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all tax entity revisions") || $account->hasPermission('administer tax entity entities')));
    $delete_permission = (($account->hasPermission("delete all tax entity revisions") || $account->hasPermission('administer tax entity entities')));

    $rows = [];

    $vids = $tax_entity_storage->revisionIds($tax_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\tax_entity\TaxEntityInterface $revision */
      $revision = $tax_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $tax_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.tax_entity.revision', ['tax_entity' => $tax_entity->id(), 'tax_entity_revision' => $vid]));
        }
        else {
          $link = $tax_entity->link($date);
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
              Url::fromRoute('entity.tax_entity.translation_revert', [
                'tax_entity' => $tax_entity->id(),
                'tax_entity_revision' => $vid,
                'langcode' => $langcode
              ]) :
              Url::fromRoute('entity.tax_entity.revision_revert', [
                'tax_entity' => $tax_entity->id(),
                'tax_entity_revision' => $vid
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.tax_entity.revision_delete', ['tax_entity' => $tax_entity->id(), 'tax_entity_revision' => $vid]),
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

    $build['tax_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
