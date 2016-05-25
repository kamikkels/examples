<?php
/**
 *  Project: Dinner Time
 *  File: dinnerTime.php
 *
 *  Page for determining who buys dinner this week
 *
 *  @author     Kerry MR <github@kerrymr.com>
 *  @Copywright 2014 Kamikkels
 */
include dirname(__FILE__) . '/links.inc';
include dirname(__FILE__) . '/header.php';

$scripts = "";
$items = array('dinner',
               'drinks',
               'snacks',
               'alcohol',
               'nothing',
               'nothing');

$sql = "SELECT `name`
        FROM `people`
        WHERE `active` = 1
        AND `attendance` < DATE_ADD(NOW(), INTERVAL (6 - DAYOFWEEK(NOW())) DAY)
        ORDER BY `last_dinner` ASC;";

$res = $db->query($sql);
print '<br><br><div id="attending" class="well">';
if($res->num_rows < 1){
    print 'no one this week :[';
    $scripts = '<script type="text/javascript">';
}

while($p = $res->fetch_assoc()){
    if($scripts == ""){
        print '<a href="#" id="'.$p['name'].'" data-pk="'.$p['name'].'"><h1 style="display: inline;">'.
              $p['name'].'</h1></a><h1 style="display: inline;"> buys '.array_shift($items).'</h1><br>';
        $scripts = '<script type="text/javascript">
                    $(\'#'.$p['name'].'\')'.$jsbit.PHP_EOL;
    } else {
        print '<br><a href="#" id="'.$p['name'].'" data-pk="'.$people[$i].'">'.$p['name'].'</a> buys '.
              array_shift($items);
        $scripts .= '$(\'#'.$p['name'].'\')'.$jsbit.PHP_EOL;
    }
}

print '</div>';

$sql = "SELECT `name`
        FROM `people`
        WHERE `active` = 1
        AND `attendance` >= DATE_ADD(NOW(), INTERVAL (6 - DAYOFWEEK(NOW())) DAY)
        ORDER BY `last_dinner` ASC;";
$res = $db->query($sql);
if($res->num_rows > 0){
    print '<br><div id="not_attending" class="well">
           <h3 style="display: inline;">Not Attending this week:</h3><br>';
    while($p = $res->fetch_assoc()){
        print '<a href="#" id="'.$p['name'].'" data-pk="'.$p['name'].'">'.$p['name'].'</a><br>';
        $scripts .= '$(\'#'.$p['name'].'\')'.$jsbit.PHP_EOL;
    }
    print '</div>'.PHP_EOL;
}


include dirname(__FILE__).'/footer.php';
print $scripts.'</script>';
print'
    </body>
</html>';
