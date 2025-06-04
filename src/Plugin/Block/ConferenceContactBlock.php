<?php


/**
 * @file
 * Contains \Drupal\emtp_conference_leads\Plugin\Block.
 */
namespace Drupal\emtp_conference_leads\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'ConferenceContactBlock' block.
 *
 * @Block(
 *   id = "conference_contact_block",
 *   admin_label = @Translation("Email Subscribe Block")
 * )
 */
class ConferenceContactBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return the render array of the form
    return \Drupal::formBuilder()->getForm('Drupal\emtp_conference_leads\Form\ConferenceContactForm');
    
  }

}
