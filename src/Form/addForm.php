<?php

namespace Drupal\drupal_users_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupal_users_list\Service\UsersService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class addForm extends FormBase{

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
    public function getFormId(){
        return 'drupal_users_list.add';
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        $form['image_element'] = [
            '#markup' => '<img class="zoom" src="https://amagno.co.uk/wp-content/uploads/2018/03/Personal-data-privacy.jpg">'
        ];
        
        $form['#attached']['library'][] = 'drupal_users_list/users_list_libraries';
        $form['#attached']['library'][] = 'claro/global-styling';

        $form['personal_data'] = [
            '#type' => 'fieldset',
            '#title' => 'Personal data',
            '#attributes' => [
                'class' => ['my-fieldset']
            ],
        ];

        $form['personal_data']['name'] = [
            '#type' => 'textfield',
            '#title' => 'Your name',
            '#required' => TRUE,
        ];

        $form['personal_data']['surname'] = [
            '#type' => 'textfield',
            '#title' => 'Your surname',
            '#required' => TRUE
        ];

        $form['personal_data']['email'] = [
            '#type' => 'email',
            '#title' => 'Your email',
        ];

        $form['institutional_data'] = [
            '#type' => 'details',
            '#title' => 'Institutional data',
            '#open' => TRUE,
        ];

        $form['institutional_data']['phone'] = [
            '#type' => 'tel',
            '#title' => 'Your number phone',
            '#required' => TRUE,
        ];
        $form['institutional_data']['hiring_date'] = [
            '#type' => 'date',
            '#title' => $this->t('Hiring date'),
            '#required' => TRUE,
        ];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
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

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $phone = $form_state->getValue('phone');
        $email = $form_state->getValue('email');

        if (strlen($phone) < 3) {
            $form_state->setErrorByName('phone', $this->t('This number is too short.'));
        }
        if ($email) {
            $findme = '@';
            if (!strpos($email, $findme)) {
                $form_state->setErrorByName('email', $this->t('Is not an email'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $data = [
            'name' => $form_state->getValue('name'),
            'surname' => $form_state->getValue('surname'),
            'email' => $form_state->getValue('email'),
            'phone' => $form_state->getValue('phone'),
            'date' => $form_state->getValue('hiring_date'),            
        ];
        $result = $this->usersService->setUser($data);
        if ($result) {
            $message = $this->t('User @name @surname succesfully registered',[
                '@name' => $form_state->getValue('name'),
                '@surname' => $form_state->getValue('surname')
            ]);
            $this->messenger()->addMessage($message);
        }else{
            $message = $this->t('User @name @surname was not registered',[
                '@name' => $form_state->getValue('name'),
                '@surname' => $form_state->getValue('surname')
            ]);
            $this->messenger()->addError($message);
        }
        //$form_state->setRedirect('drupal_users_list.list');

        return;
    }

}