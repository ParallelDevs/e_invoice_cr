<?php

namespace Drupal\invoice_received_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface;

/**
 * Class InvoiceReceivedEntityController.
 *
 *  Returns responses for Invoice received entity routes.
 */
class InvoiceReceivedEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Invoice received entity  revision.
   *
   * @param int $invoice_received_entity_revision
   *   The Invoice received entity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($invoice_received_entity_revision) {
    $invoice_received_entity = $this->entityManager()->getStorage('invoice_received_entity')->loadRevision($invoice_received_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('invoice_received_entity');

    return $view_builder->view($invoice_received_entity);
  }

  /**
   * Page title callback for a Invoice received entity  revision.
   *
   * @param int $invoice_received_entity_revision
   *   The Invoice received entity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($invoice_received_entity_revision) {
    $invoice_received_entity = $this->entityManager()->getStorage('invoice_received_entity')->loadRevision($invoice_received_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $invoice_received_entity->label(), '%date' => format_date($invoice_received_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates a overview table of older revisions of a Invoice received entity.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $invoice_received_entity
   *   A Invoice received entity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InvoiceReceivedEntityInterface $invoice_received_entity) {
    $account = $this->currentUser();
    $langcode = $invoice_received_entity->language()->getId();
    $langname = $invoice_received_entity->language()->getName();
    $languages = $invoice_received_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $invoice_received_entity_storage = $this->entityManager()->getStorage('invoice_received_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $invoice_received_entity->label()]) : $this->t('Revisions for %title', ['%title' => $invoice_received_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all invoice received entity revisions") || $account->hasPermission('administer invoice received entity entities')));
    $delete_permission = (($account->hasPermission("delete all invoice received entity revisions") || $account->hasPermission('administer invoice received entity entities')));

    $rows = [];

    $vids = $invoice_received_entity_storage->revisionIds($invoice_received_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\invoice_received_entity\InvoiceReceivedEntityInterface $revision */
      $revision = $invoice_received_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $invoice_received_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.invoice_received_entity.revision', ['invoice_received_entity' => $invoice_received_entity->id(), 'invoice_received_entity_revision' => $vid]));
        }
        else {
          $link = $invoice_received_entity->link($date);
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
              Url::fromRoute('entity.invoice_received_entity.translation_revert', ['invoice_received_entity' => $invoice_received_entity->id(), 'invoice_received_entity_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.invoice_received_entity.revision_revert', ['invoice_received_entity' => $invoice_received_entity->id(), 'invoice_received_entity_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.invoice_received_entity.revision_delete', ['invoice_received_entity' => $invoice_received_entity->id(), 'invoice_received_entity_revision' => $vid]),
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

    $build['invoice_received_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function acceptInvoice($id) {

  }

  /**
   * {@inheritdoc}
   */
  public function rejectInvoice($id) {

  }

}
