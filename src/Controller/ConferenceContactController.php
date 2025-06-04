<?php
namespace Drupal\emtp_conference_leads\Controller;
/**
* to display the form in a ConferenceController for routing
*
*/
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;

class ConferenceContactController extends ControllerBase {
  public function displayForm() {
    // Use the form builder to create your form
    $form = \Drupal::formBuilder()->getForm('Drupal\emtp_conference_leads\Form\ConferenceContactForm');
    
    // Return a render array that includes the form
    return [
      '#theme' => 'contact_form',  // Optional custom template suggestion
      'form' => $form,
    ];
  }
}
