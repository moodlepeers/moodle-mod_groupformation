<?php
$tasks = array(
    array(
        'classname' => 'mod_groupformation\task\build_groups_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'mod_groupformation\task\archive_activity_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '1',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);