<?php

namespace Drupal\emtp_conference_leads\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides an Email subscription form with AJAX functionality.
 */
class ConferenceContactForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'conference_leads_form';
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
   // Load conferences from the config.
    $config = \Drupal::config('emtp_conference_leads.conference_settings');
    $conferences = $config->get('conferences') ?: [];

    // Get the 'conference' parameter from the URL.
    $conference_id = \Drupal::request()->query->get('conference');
    
    // Check if the conference exists.
    if (isset($conferences[$conference_id])) {
      $selected_conference = $conferences[$conference_id];
    }
    else {
      // Fallback behavior if no valid conference is provided in the URL.
      \Drupal::messenger()->addError($this->t('Invalid conference specified.'));
       // Perform logic and then redirect to front page
       /*
          $url = Url::fromRoute('<front>')->toString();
          $response = new RedirectResponse($url);
          $response->send();
          
          // Since a redirect was sent, stop any further processing
        return;
       */
      return $form; // Optionally, return an empty form or redirect.
    }

    // Display the conference title (not as an input, just info for the user).
    $form['conference_title'] = [
      '#markup' => '<h2>' . $this->t('Sign up for: @conference', ['@conference' => $selected_conference['title']]) . '</h2>',
    ];

    // Hidden field to store the conference ID.
    $form['conference'] = [
      '#type' => 'hidden',
      '#value' => $conference_id,
    ];

    $form['firstname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
    ];

    $form['lastname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#id' => 'conference-contact-form-submit', // Unique ID for the submit button
    ];
    $form['#attached']['library'][] = 'emtp_conference_leads/contact_form_email_styles';
    $form['#theme'] = 'contact_form';
    
    return $form;
  
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
    // This function is required but not used for AJAX submissions.
    $values = $form_state->getValues();
    //\Drupal::logger('conference_leads_form')->info("EMTP email subscribe submit fired.");
    
    $conference_id = $form_state->getValue('conference');
    // Load conferences from the config.
    $config = \Drupal::config('emtp_conference_leads.conference_settings');
    $conferences = $config->get('conferences') ?: [];
    $hubspot_api = $config->get('hubspot_api') ?: [];
    $hubspot_url = $config->get('hubspot_url') ?: [];
    
    try{
          // Make sure the conference ID is valid.
          if (isset($conferences[$conference_id])) {
            $selected_conference = $conferences[$conference_id];
            $this->conference_leads_form_insert_internal_db($values);
            //$this->conference_leads_send_hubspot_list($values);
            // Log or save form data.
            \Drupal::logger('emtp_conference_leads')->notice('New contact for @conference: @name, @email', [
              '@conference' => $selected_conference['title'],
              '@name' => $name,
              '@email' => $email,
            ]);

            // Display a success message.
            \Drupal::messenger()->addMessage($this->t('Thank you for signing up for @conference.', ['@conference' => $selected_conference['title']]));
          }
          else {
            // Handle invalid conference.
            \Drupal::messenger()->addError($this->t('emtp_conference_leads Invalid conference.'));
          }

         
          \Drupal::messenger()->addMessage($this->t('<h4>Thank You!</h4> We are so glad you are connecting with us!'));
          
          
    } catch(Exception $ex){
        \Drupal::logger('emtp_conference_leads')->error($ex->getMessage());
    }
    
    // Redirect to front page after form submission
    $url = Url::fromRoute('<front>')->toString();
    $response = new RedirectResponse($url);
    $response->send();
    
    // If you're returning a response, you don't need to return a $form array
    return;
    
  }
  
  
/**
   * Insert data into Internal database.
   */
  public function conference_leads_form_insert_internal_db($values) {
          
    // Insert the form data into the custom table.
    try {    
      $result = $this->database->insert('conference_leads_form_submissions')
            ->fields([
              'ip_address' => \Drupal::request()->getClientIp(),
              'email' => $values['email'],
              'firstname' => $values['firstname'],
              'lastname' => $values['lastname'],
              'conference' => $values['conference'],
              'created' => \Drupal::time()->getRequestTime(),
            ]);
  
    }
    catch (Exception $e) {
      // Log the exception to watchdog.
      \Drupal::logger('emtp_conference_leads')
        ->error($e->getMessage());
    }
 
  
  }
  
 

  public function conference_leads_send_hubspot_list($values){
        // Load conferences from the config.
      $config = \Drupal::config('emtp_conference_leads.conference_settings');
      $conference_id = $form_state->getValue('conference');
      $hubspot_api_key = $config->get('hubspot_api_key'); //the authentication token 
      $hubspot_url = $config->get('hubspot_url'); // the contact api to list functionality.

      
      $auth = 'Bearer ' . $hubspot_api_key;	
     
      
      
      try{
        $response = \Drupal::httpClient()->post($hubspot_url, [
          'verify' => true,
          'json' => $data,
          'headers' => [
            'Authorization' => $auth,
            'Content-type' => 'application/json',
          ],
        ])->getBody()->getContents();

        $decoded = json_decode($response, true);
       // $this->messenger()->addStatus($this->t('<h4>Thank You!</h4> '.$decoded["inlineMessage"] .' We are so glad you are joining our community!</div>'));
       // \Drupal::logger('emtp_email_subscribe')->info('submission post success! at Email newsletter form', ['operations' => 'HubSpot Remote API Post', 'response' => $decoded['inlineMessage']]);

      } catch (\GuzzleHttp\Exception\GuzzleException $error) {
        // Get the original response
        $response = $error->getResponse();
        // Get the info returned from the remote server.
        $response_info = $response->getBody()->getContents();
        // Using FormattableMarkup allows for the use of <pre/> tags, giving a more readable log item.
        $message = new \Drupal\Component\Render\FormattableMarkup('API connection error. Error details are as follows:<pre>@response</pre>', ['@response' => print_r(json_decode($response_info), TRUE)]);
        // Log the error

        \Drupal::logger('emtp_conference_leads')->error('submission remote post failed at guzzle level Contact us form', [ '@error'=>$error->getMessage()]);
      }
      catch (\Exception $error) {
        // Log the error.
        //deprecated watchdog_exception('Remote API Connection', $error, t('An unknown error occurred while trying to connect to the remote API. This is not a Guzzle error, nor an error in the remote API, rather a generic local error occurred. The reported error was @error', ['@error' => $error->getMessage()]));
        \Drupal::logger('emtp_conference_leads')->error('Something errored! ', [ '@error'=>$error->getMessage()]);
      }
            
            
            
  }
  
  

}
