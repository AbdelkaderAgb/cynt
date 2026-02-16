<?php
/**
 * CYN Tourism - Database Wrapper (MVC Version)
 * 
 * Thin wrapper that loads the existing Database class from the config
 * directory. The original Database class already uses PDO with
 * prepared statements, singleton pattern, transaction support.
 * 
 * @package CYN_Tourism
 * @version 3.0.0
 */

require_once BASE_PATH . '/config/database.php';
