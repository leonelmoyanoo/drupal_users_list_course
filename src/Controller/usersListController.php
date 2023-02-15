<?php

namespace Drupal\drupal_users_list\Controller;

use Drupal\Core\Controller\ControllerBase;

class usersListController extends ControllerBase
{
    /**
     * Header of table.
     * @var array
     */
    protected $headers;

    /**
     * Manage HTML Elements
     * @var Drupal\drupal_users_list\Service\HtmlService
     */
    protected $htmlService;

    /**
     * Manage Users
     * @var Drupal\drupal_users_list\Service\UsersService
     */
    protected $usersService;

    /**
     * Current user
     * @var array
     */
    protected $user;

    public function __construct()
    {
        $this->htmlService = \Drupal::Service('drupal_users_list.html_elements');
        $this->usersService = \Drupal::Service('drupal_users_list.users_service');
        $this->user['data'] = \Drupal::currentUser();
        $this->user['seePermission'] = $this->user['data']->hasPermission('drupal_users_list list users');
        $this->user['addPermission'] = $this->user['data']->hasPermission('drupal_users_list add users');
        $this->user['editPermission'] = $this->user['data']->hasPermission('drupal_users_list edit users');
        $this->user['deletePermission'] = $this->user['data']->hasPermission('drupal_users_list delete users');
        $this->headers = ['See', 'Edit', 'Delete'];
        
        foreach ($this->headers as $key => $value) {
            $userKey = strtolower($value) . 'Permission';
            if(!$this->user[$userKey]){
                unset($this->headers[$key]);
            }
        }
    }

    public function listUser($uid)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        $content = [];
        $deleteLink = '';
        $editLink = '';
        $user = $this->usersService->getUserById($uid);

        if($this->user['editPermission']){
            $editLink = $this->htmlService->createLink('drupal_users_list.edit', 'Edit', NULL, [
                'uid' => $uid
            ]);
        }
        if($this->user['deletePermission']){
            $deleteLink = $this->htmlService->createLink('drupal_users_list.delete', 'Delete', ['action-link', 'action-link--danger', 'action-link--icon-trash'], [
                'uid' => $uid
            ]);
        }

        $content['#attached']['library'][] = 'claro/global-styling';
        $content[] = [
            '#theme' => 'users_list',
            '#user' => $user,
            '#edit' => $editLink,
            '#delete' => $deleteLink,

        ];
        return $content;
    }

    public function list()
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        $content = [];

        $content['description'] = [
            '#markup' => t('<strong> Here you can see your users </strong><br>')
        ];

        if($this->user['addPermission']){
            $content['buttons'] = [
                '#markup' => t('<i> To add a new user, click on the next button <i>')
            ];
    
            $content['addUser'] = $this->htmlService->createLink('drupal_users_list.add', 'Add user');
        }


        $rows = $this->setRowUsers($this->usersService->getUsers());
        $empty = 'No users available';
        $content['table'] = $this->htmlService->createTable($this->headers, $rows, $empty);

        $content['pager'] = $this->htmlService->createPager(10);

        return $content;
    }

    public function setRowUsers($users)
    {
        $row = [];
        foreach ($users as $key => $user) {
            $userData = [];
            $user = (array) $user;
            foreach ($user as $key => $data) {
                $userData[] = $data;
            }
            if($this->user['seePermission']){
                $seeLink = $this->htmlService->createLink('drupal_users_list.list_user', 'See', [], [
                    'uid' => $user['uid']
                ], FALSE)->toString();
                array_push($userData, $seeLink);
            }
            if($this->user['editPermission']){
                $editLink = $this->htmlService->createLink('drupal_users_list.edit', 'Edit', [], [
                    'uid' => $user['uid']
                ], FALSE)->toString();
                array_push($userData, $editLink);
            }
            if($this->user['deletePermission']){
                $deleteLink = $this->htmlService->createLink('drupal_users_list.delete', 'Delete', ['action-link', 'action-link--danger', 'action-link--icon-trash'], [
                    'uid' => $user['uid']
                ], FALSE)->toString();
                array_push($userData, $deleteLink);
            }

            $row[] = $userData;
        }
        $first_user = (array) reset($users);
        $first_user = array_reverse($first_user, TRUE);
        foreach ($first_user as $key => $user) {
            array_unshift($this->headers, ucfirst($key));
        }
        return $row;
    }
}
