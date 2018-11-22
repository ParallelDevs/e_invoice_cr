<?php

namespace Drupal\provider_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\provider_entity\Entity\ProviderEntityInterface;

/**
 * Class ProviderEntityController.
 *
 *  Returns responses for Provider routes.
 */
class ProviderEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Provider revision.
   *
   * @param int $provider_entity_revision
   *   The Provider  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($provider_entity_revision) {
    $provider_entity = $this->entityManager()->getStorage('provider_entity')->loadRevision($provider_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('provider_entity');

    return $view_builder->view($provider_entity);
  }

  /**
   * Page title callback for a Provider  revision.
   *
   * @param int $provider_entity_revision
   *   The Provider  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($provider_entity_revision) {
    $provider_entity = $this->entityManager()->getStorage('provider_entity')->loadRevision($provider_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $provider_entity->label(), '%date' => format_date($provider_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Provider .
   *
   * @param \Drupal\provider_entity\Entity\ProviderEntityInterface $provider_entity
   *   A Provider  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ProviderEntityInterface $provider_entity) {
    $account = $this->currentUser();
    $langcode = $provider_entity->language()->getId();
    $langname = $provider_entity->language()->getName();
    $languages = $provider_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $provider_entity_storage = $this->entityManager()->getStorage('provider_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $provider_entity->label()]) : $this->t('Revisions for %title', ['%title' => $provider_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all Provider revisions") || $account->hasPermission('administer Provider entities')));
    $delete_permission = (($account->hasPermission("delete all Provider revisions") || $account->hasPermission('administer Provider entities')));

    $rows = [];

    $vids = $provider_entity_storage->revisionIds($provider_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\provider_entity\ProviderEntityInterface $revision */
      $revision = $provider_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $provider_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.provider_entity.revision', ['provider_entity' => $provider_entity->id(), 'provider_entity_revision' => $vid]));
        }
        else {
          $link = $provider_entity->link($date);
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
              Url::fromRoute('entity.provider_entity.translation_revert',
                [
                  'provider_entity' => $provider_entity->id(),
                  'provider_entity_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
              Url::fromRoute('entity.provider_entity.revision_revert',
                [
                  'provider_entity' => $provider_entity->id(),
                  'provider_entity_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.provider_entity.revision_delete', ['provider_entity' => $provider_entity->id(), 'provider_entity_revision' => $vid]),
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

    $build['provider_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
