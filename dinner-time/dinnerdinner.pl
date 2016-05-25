#!/usr/local/bin/perl
# Part of the DinnerTime project
# Don't reuse this code, it's terrible
use DBI;
my $dbh = DBI->connect('dbi:mysql:dinner_time:localhost:3306',
                       'dinner_time',
                       'dinnerdinner', );

$dbh->prepare('UPDATE `people`
               SET `last_dinner` = DATE(NOW()),
                   `dinners_bought` = dinners_bought + 1
               WHERE `id` = (SELECT `id`
                             FROM (SELECT * FROM `people`) AS p
                             WHERE `attendance` <= DATE(NOW())
                             ORDER BY `last_dinner` ASC
                             LIMIT 1);')->execute();
                             
$dbh->prepare('UPDATE `people`
               SET `dinners_skipped` = dinners_skipped + 1               
               WHERE `attendance` >= DATE(NOW())
               AND `last_dinner` = (SELECT MIN(`last_dinner`)
                                    FROM (SELECT * FROM `people`) AS p
                                    GROUP BY `active` DESC
                                    LIMIT 1);')->execute();
