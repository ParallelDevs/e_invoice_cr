<?php

namespace Drupal\exoneration_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\exoneration_entity\Entity\ExonerationEntityInterface;

/**
 * Class ExonerationEntityController.
 *
 *  Returns responses for Exoneration entity routes.
 */
class ExonerationEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Exoneration entity  revision.
   *
   * @param int $exoneration_entity_revision
   *   The Exoneration entity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($exoneration_entity_revision) {
    $exoneration_entity = $this->entityManager()->getStorage('exoneration_entity')->loadRevision($exoneration_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('exoneration_entity');

    return $view_builder->view($exoneration_entity);
  }

  /**
   * Page title callback for a Exoneration entity  revision.
   *
   * @param int $exoneration_entity_revision
   *   The Exoneration entity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($exoneration_entity_revision) {
    $exoneration_entity = $this->entityManager()->getStorage('exoneration_entity')->loadRevision($exoneration_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $exoneration_entity->label(), '%date' => format_date($exoneration_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Exoneration entity .
   *
   * @param \Drupal\exoneration_entity\Entity\ExonerationEntityInterface $exoneration_entity
   *   A Exoneration entity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ExonerationEntityInterface $exoneration_entity) {
    $account = $this->currentUser();
    $langcode = $exoneration_entity->language()->getId();
    $langname = $exoneration_entity->language()->getName();
    $languages = $exoneration_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $exoneration_entity_storage = $this->entityManager()->getStorage('exoneration_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $exoneration_entity->label()]) : $this->t('Revisions for %title', ['%title' => $exoneration_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all exoneration entity revisions") || $account->hasPermission('administer exoneration entity entities')));
    $delete_permission = (($account->hasPermission("delete all exoneration entity revisions") || $account->hasPermission('administer exoneration entity entities')));

    $rows = [];

    $vids = $exoneration_entity_storage->revisionIds($exoneration_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\exoneration_entity\ExonerationEntityInterface $revision */
      $revision = $exoneration_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $exoneration_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.exoneration_entity.revision', ['exoneration_entity' => $exoneration_entity->id(), 'exoneration_entity_revision' => $vid]));
        }
        else {
          $link = $exoneration_entity->link($date);
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
              Url::fromRoute('entity.exoneration_entity.translation_revert', [
                'exoneration_entity' => $exoneration_entity->id(),
                'exoneration_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.exoneration_entity.revision_revert', [
                'exoneration_entity' => $exoneration_entity->id(),
                'exoneration_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.exoneration_entity.revision_delete', ['exoneration_entity' => $exoneration_entity->id(), 'exoneration_entity_revision' => $vid]),
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

    $build['exoneration_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
