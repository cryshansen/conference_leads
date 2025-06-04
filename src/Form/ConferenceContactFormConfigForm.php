<?php

/**
 * @file
 * Contains \Drupal\custom_module\Form\ConferenceContactFormConfigForm.
 */
namespace Drupal\custom_module\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class YourModuleConfigForm.
 */
class ConferenceContactFormConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['conference_leads.conference_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'conference_leads_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

        
    $config = $this->config('conference_leads.conference_settings');
    $conferences = $config->get('conferences') ?: [];

    // Conference title and date.
    $form['conferences'] = [
      '#type' => 'table',
      '#header' => [$this->t('Title'), $this->t('Date'), $this->t('Operations')],
      '#empty' => $this->t('No conferences added yet.'),
    ];

    foreach ($conferences as $index => $conference) {
      $form['conferences'][$index]['title'] = [
        '#type' => 'textfield',
        '#default_value' => $conference['title'],
        '#title' => $this->t('Conference Title'),
      ];
      $form['conferences'][$index]['date'] = [
        '#type' => 'date',
        '#default_value' => $conference['date'],
        '#title' => $this->t('Conference Date'),
      ];
      $form['conferences'][$index]['remove'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Remove'),
      ];
    }

    // Add more conferences.
    $form['add_conference'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Another Conference'),
      '#submit' => ['::addConferenceSubmit'],
    ];
    
    $form['use_hubspot'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Check if using hubspot api.'),
      ];
    
    $form['hubspot_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hubspot API Key'),
      //'#required' => TRUE,
    ];
    
    $form['hubspot_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hubspot API endpoint to use'),
      //'#required' => TRUE,
    ];
    
    return parent::buildForm($form, $form_state);
    
  }
  
/**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    parent::validateForm($form, $form_state);
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      
    $conferences = $form_state->getValue('conferences');
    $hubspot_api_key = $form_state->getValue('hubspot_api_key');
    $hubspot_url = $form_state->getValue('hubspot_url');
    
    //$tags = $form_state->getValue('tags') ?? [];
    $filtered_rows = [];
    foreach ($conferences as $conference) {
      if (empty($conference['remove'])) {
        $filtered_rows[] = [
          'title' => $conference['title'],
          'date' => $conference['date'],
        ];
      }
    }
    
    $this->config('conference_leads.conference_settings')
      ->set('conferences', $filtered_rows)
      ->set('hubspot_api_key', $hubspot_api_key) 
      ->set('hubspot_url', $hubspot_url) 
      ->save();

    parent::submitForm($form, $form_state);
      
  }
  
   /**
   * Adds another conference to the form dynamically.
   */
  public function addConferenceSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }
  
}