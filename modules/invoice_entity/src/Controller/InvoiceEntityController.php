<?php

namespace Drupal\invoice_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\invoice_entity\Entity\InvoiceEntityInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class InvoiceEntityController.
 *
 *  Returns responses for Invoice routes.
 */
class InvoiceEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Invoice  revision.
   *
   * @param int $invoice_entity_revision
   *   The Invoice  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($invoice_entity_revision) {
    $invoice_entity = $this->entityManager()->getStorage('invoice_entity')->loadRevision($invoice_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('invoice_entity');

    return $view_builder->view($invoice_entity);
  }

  /**
   * Page title callback for a Invoice  revision.
   *
   * @param int $invoice_entity_revision
   *   The Invoice  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($invoice_entity_revision) {
    $invoice_entity = $this->entityManager()->getStorage('invoice_entity')->loadRevision($invoice_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $invoice_entity->label(), '%date' => format_date($invoice_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Invoice .
   *
   * @param \Drupal\invoice_entity\Entity\InvoiceEntityInterface $invoice_entity
   *   A Invoice  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InvoiceEntityInterface $invoice_entity) {
    $account = $this->currentUser();
    $langcode = $invoice_entity->language()->getId();
    $langname = $invoice_entity->language()->getName();
    $languages = $invoice_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $invoice_entity_storage = $this->entityManager()->getStorage('invoice_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $invoice_entity->label()]) : $this->t('Revisions for %title', ['%title' => $invoice_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all invoice revisions") || $account->hasPermission('administer invoice entities')));
    $delete_permission = (($account->hasPermission("delete all invoice revisions") || $account->hasPermission('administer invoice entities')));

    $rows = [];

    $vids = $invoice_entity_storage->revisionIds($invoice_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\invoice_entity\InvoiceEntityInterface $revision */
      $revision = $invoice_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $invoice_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.invoice_entity.revision', ['invoice_entity' => $invoice_entity->id(), 'invoice_entity_revision' => $vid]));
        }
        else {
          $link = $invoice_entity->link($date);
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
              Url::fromRoute('entity.invoice_entity.translation_revert', [
                'invoice_entity' => $invoice_entity->id(),
                'invoice_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.invoice_entity.revision_revert', [
                'invoice_entity' => $invoice_entity->id(),
                'invoice_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.invoice_entity.revision_delete', ['invoice_entity' => $invoice_entity->id(), 'invoice_entity_revision' => $vid]),
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

    $build['invoice_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Validate a invoice.
   *
   * @param string $key
   *   A Invoice  object.
   * @param string $id
   *   A Invoice Id.
   *
   * @return bool
   *   An array as expected by drupal_render().
   */
  public function validateInvoice($key, $id) {
    /** @var \Drupal\invoice_entity\InvoiceService $invoice_service */
    $invoice_service = \Drupal::service('invoice_entity.service');
    $entity = \Drupal::entityManager()->getStorage('invoice_entity')->load($id);
    $result = $invoice_service->validateInvoiceEntity($entity);

    if (is_null($result['response'])) {
      drupal_set_message(t("Status Unknown. The state couldn't be validated."), 'error');
    }
    else {
      if ($result['state'] === "rejected") {
        drupal_set_message(t("Status Rejected. @text", ["@text" => $result['response'][3]->DetalleMensaje]), 'error');
      }
      elseif ($result['state'] === "published") {
        drupal_set_message(t("Status Accepted. @text", ["@text" => $result['response'][3]->DetalleMensaje]), 'status');
      }
      drupal_set_message(t('A validation request has been performed.'), 'status');
    }
    return new RedirectResponse('/admin/structure/e-invoice-cr/invoice_entity');
  }

}
