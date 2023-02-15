<?php

namespace Drupal\drupal_users_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupal_users_list\Service\UsersService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class deleteForm extends FormBase {
    /**
     * Manage users.
     * @var Drupal\drupal_users_list\Service\UsersService
     */
    protected $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    public static function create(ContainerInterface $container){
        return new static(
            $container->get('drupal_users_list.users_service'),
        );
    }
  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId(){
    return 'drupal_users_list.delete';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid=NULL){
    $user = $this->usersService->getUserById($uid);
    $form['#attached']['library'][] = 'claro/global-styling';
    $form['description'] = [
        '#markup' => $this->t('Are you going to delete @name @surname (@uid)',[
            '@name' => $user->name,
            '@surname' => $user->surname,
            '@uid' => $user->uid,
        ])
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Confirm'),
        '#button_type' => 'primary',
        '#attributes' => [
            'class' => ['my-button']
        ],
    ];
    $form['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#submit' => ['cancel'],
        '#limit_validation_errors' => [],
        '#attributes' => [
            'class' => ['my-button']
        ],
    ];
    $form['uid'] = [
        '#type' => 'hidden',
        '#value' => $uid
    ];
    return $form;
  }

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state){

  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $userId = $form_state->getValue('uid');
    $result = $this->usersService->deleteUser($userId);
    if ($result) {
        $message = $this->t('User @name @surname succesfully deleted',[
            '@name' => $form_state->getValue('name'),
            '@surname' => $form_state->getValue('surname')
        ]);
        $this->messenger()->addMessage($message);
        $form_state->setRedirect('drupal_users_list.list');
    }else{
        $message = $this->t('User @name @surname was not deleted',[
            '@name' => $form_state->getValue('name'),
            '@surname' => $form_state->getValue('surname')
        ]);
        $this->messenger()->addError($message);
    }
  }
}