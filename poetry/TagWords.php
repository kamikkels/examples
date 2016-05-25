<?php
/**
 *  Project: Poetry App
 *  File: TagWords.php
 *
 *  Page for tagging words in the word list
 *
 *  @author     Kerry MR <github@kerrymr.com>
 *  @Copywright 2013 Kamikkels
 */
include dirname(__FILE__) . '/links.inc';

//set style
if(isset($_POST['style'])) {
    $style = $_POST['style'];
}else {
    $style = 'default';
}

//Count the words in the database
$query = 'SELECT count(*) as wn
          FROM   words';
$result  = $db->query($query);
$numWords = $result->fetch_assoc();
print_r($numWords);
$numWords = $numWords['wn'];

//work out where in the list we are, and were we can goto using to buttons
if(isset($_POST['back'])) {
    $start = $_POST['back'];
    $end = $start + 50;
}else if(isset($_POST['start'])) {
    $start = $_POST['start'];
    $end = $start + 50;
}else {
    $start = 0;
}

if(isset($_POST['next'])) {
    $start = $_POST['next'];
    $end = $start + 50;
}

//see if the user wants to jump to a word
if(isset($_POST['jump']) && is_numeric($_POST['jump'])){
    $start = $_POST['jump'] - ($_POST['jump']%50);
    $end = $start + 50;
}else if(isset($_POST['jump']) && $_POST['jump'] != ''){
    $query = 'SELECT wordid
              FROM   words
              WHERE  `lemma` LIKE "'.$db->mysqli_escape_string($_POST['jump']).'"
              LIMIT 0 , 1';
    $result  = $db->query($query);
    $wj = $result->fetch_assoc();
    $start = $wj['wordid'] - ($wj['wordid']%50);
    $end = $start + 50;
    $result->close();
}

//set the back and next values
if(($start - 50) < 0) {
    $back = 0;
    $next = 100;
    $start = 0;
    $end = 50;
}else if(($start + 50) >= $numWords){
    $start = ($numWords - ($numWords%50));
    $end = $numWords;
    $back = $start - 50;
    $next = $numWords;
}else {
    $back = $start - 50;
    $next = $end;
}

//get part of the words table, check if there are changes to it
$query = 'SELECT wordid,
                 lemma,
                 tags,
                 types,
                 syllables
          FROM   words
          LIMIT  '.$start.' , 50';
$result  = $db->query($query);

if(isset($_POST['submit'])) {
    $query = '';
    $delQuery = '';
    while($data = $result->fetch_assoc()) {
        
        if(isset($_POST['update'][$data['wordid']]) && ($_POST['update'][$data['wordid']]['tags'] != $data['tags'] || 
                 $_POST['update'][$data['wordid']]['types'] != $data['types'] || $_POST['update'][$data['wordid']]['syllables'] != $data['syllables'])) {
            $query .= 'UPDATE wordnet30.words 
                       SET    tags = "'. $db->mysqli_escape_string($_POST['update'][$data['wordid']]['tags']).'", 
                              types = "'. $db->mysqli_escape_string($_POST['update'][$data['wordid']]['types']).'", 
                              syllables = '.$db->mysqli_escape_string($_POST['update'][$data['wordid']]['syllables']).' 
                       WHERE  words.wordid = '.$db->mysqli_escape_string($data['wordid']).';';
            $query .= "\n";
        }else if(isset($_POST['update'][$data['wordid']]) && $_POST['update'][$data['wordid']]['delete']) {
            $delQuery .= 'DELETE FROM `wordnet30`.`words` 
                          WHERE `words`.`wordid` = '.$data['wordid'].' 
                          AND `words`.`lemma` 
                          LIKE "'.$data['lemma'].'"; ';
        }
    }

    if($query != '') {
        $db->multi_query($query);
        while ($db->next_result()) {;}
    }

/**
 * following query will correct the auto_increment values after some deletes:
    SET @var_name = 0;
    UPDATE `wordnet30`.`words` 
    SET `words`.`wordid` = (@var_name := @var_name +1);
*/
    if($delQuery != '') {
        $db->multi_query($delQuery);
        while ($db->next_result()) {;}
    }
    
    $query = 'SELECT  wordid,
                      lemma,
                      tags,
                      types,
                      syllables
              FROM    words
              LIMIT '.$start.' , 50';
    $result  = $db->query($query);
}

// setup the words for display
$output = '';
while($data =  $result->fetch_assoc()) {
    $id = $data['wordid'];
    $output .= '<tr><td>'.$id.'</td><td>'.$data['lemma'].'</td><td><input type="text" name="update['.$id.'][tags]" size="100" value="'.$data['tags'].'"></td>
                <td><input type="text" name="update['.$id.'][types]" size="50" value="'.$data['types'].'"></td>
                <td><input type="text" name="update['.$id.'][syllables]" size="2" value="'.$data['syllables'].'"></td><td>&nbsp</td>
                <td><input type="checkbox" name="update['.$id.'][delete]" value="update['.$id.'][delete]"></td></tr>';
}

//output the html
print '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; iso-8859-1" />
    <link href="'.$style.'.css" rel="stylesheet" type="text/css" />
    <title>Poetry App Word Tagging</title>
</head>
<body>
    <div class="divHead">
        <form method="post" action="'.$_SERVER['PHP_SELF'].'">
            <input type="hidden" name="start" value="'.$start.'">
            <table class="jumpTable">
                <tr>
                    <td>Jump to word:</td><td><input type="text" name="jump"></td><td><input type="submit" value="go" name="go"></td>
                </tr>
            </table>
        </form>
    </div>
    <div class="divBody">
        <form method="post" action="'.$_SERVER['PHP_SELF'].'">
            <table class="mainTable">
                <tr>
                    <td><input type="submit" value="'.$back.'" name="back"></td>
                    <td colspan="3" align="center"><input type="submit" value="submit" name="submit"><input type="hidden" name="start" value="'.$start.'"></td>
                    <td><input type="submit" value="'.$next.'" name="next"></td>
                </tr>
                <tr>
                    <th>#</th><th>Word</th><th>Tags</th><th>Classes</th><th>Syllables</th><td>&nbsp</td><th>Delete?</th>
                </tr>
                    '.$output.'
                <tr>
                    <td><input type="submit" value="'.$back.'" name="back"></td>
                    <td colspan="3" align="center"><input type="submit" value="submit" name="submit"><input type="hidden" name="start" value="'.$start.'"></td>
                    <td><input type="submit" value="'.$next.'" name="next"></td>
                </tr>
            </table>
        </form>
    </div>
</body>';