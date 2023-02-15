<?php

namespace Drupal\drupal_users_list\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheBackendInterface;

class UsersService
{
    /**
     * Manage Database
     * @var Drupal\Core\Database\Connection
     */
    protected $database;
    /**
     * Manage Cache
     * @var Drupal\Core\Cache\CacheBachendInterface
     */
    protected $cache;
    /**
     * Cache Users id
     * @var String
     */
    protected $cid;

    public function __construct(Connection $database, CacheBackendInterface $cache)
    {
        $this->database = $database;
        $this->cache = $cache;
        $this->cid = 'users_list:users';
    }

    public function getUsers($limit = NULL)
    {
        $users = $this->cache->get($this->cid);
        if ($users) {
            return $users->data;
        }
        $query = $this->database->select('my_users_data', 'users');
        if ($limit) {
            $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
                ->limit(10);
        }
        $query->fields('users');
        $users = $query->execute();
        $rows = [];
        foreach ($users as $key => $row) {
            $rows[$row->uid] = $row;
        }
        $this->cache->set($this->cid, $rows, time() + 3600);
        return $rows;
    }
    public function getUserById($uid)
    {
        $users = $this->cache->get($this->cid);
        if ($users) {
            return $users->data[$uid];
        }

        $query = $this->database->select('my_users_data', 'users');
        $query->condition('users.uid', $uid);
        $query->fields('users');
        $user = $query->execute();
        return $user->fetch();
    }
    public function setUser($data)
    {
        $result = $this->database->insert('my_users_data')
            ->fields($data)
            ->execute();
        if ($result) {
            $this->cache->delete($this->cid);
        }
        return $result;
    }
    public function updateUserById($data, $uid)
    {
        $users = $this->cache->get($this->cid);
        $result = FALSE;
        if ($users) {
            $users = $users->data;
            $result = $this->database->update('my_users_data')
                ->fields($data)
                ->condition('uid', $uid)
                ->execute();
            if ($result) {
                $users[$uid] = $data;
                $this->cache->set($this->cid, $users, time() + 3600);
            }
        }
        return $result;
    }
    public function deleteUser($uid)
    {
        $result = $this->database->delete('my_users_data')
            ->condition('uid', $uid)
            ->execute();

        $users = $this->cache->get($this->cid);
        if ($result && $users) {
            $users = $users->data;
            unset($users[$uid]);
            $this->cache->set($this->cid, $users, time() + 3600);
        }
        return $result;
    }
}
