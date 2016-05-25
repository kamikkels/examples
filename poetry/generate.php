<?php
/**
 *  Project: Poetry App
 *  File: generate.php
 *
 *  Functions for sentence generation
 *
 *  @author     Kerry MR <github@kerrymr.com>
 *  @Copywright 2013 Kamikkels
 */


/**
 * returns a formated XP or $length syllables length for printing
 * @param int
 * @return string
 */
function XP($length) {
    if(mt_rand(1, 2) == 1){
        return specifier() . xBar($length);
    }else {
        return xBar($length) . specifier();
    }
}

/**
 * returns a random order value
 * @return int
 */
function specifier() {
    if(mt_rand(1, 2) == 1) {
        return spec('det');
    }else {
        return spec('aux');
    }
}

function spec($type) {
    if($type === 'det') {
        return getWord(0, 'Determiner');
    }else {
        return getWord(0, 'Auxiliary');
    }
}


/**
 * X-Bar = either (X-Bar, adjunct) or (X, (Compliments (0-N)))
 * @param int
 * @return string
 */
function xBar($length) {
    $go = mt_rand(1,4);
    if($go == 1) {
        $split = mt_rand(1,($length-1));
        $word = getWord(($length - $split), '');
        if(mt_rand(1,2)) {
            return $word['lemma'].' '.xBar($split);
        }else {
            return xBar($split).' '.$word['lemma'];
        }
    }else if($go == 2) {
        $split = mt_rand(1,$length);
        if(mt_rand(1,2)) {
            return compliment($length - $split).xBar($split);
        }else {
            return xBar($split).compliment($length - $split);
        }
    }else {
        $word = getWord($length, '');
        if(mt_rand(1,10) == 7){
            return $word['lemma'].', ';
        }
        return $word['lemma'].' ';
    }
}


/**
 * Returns between 0 and N compliments
 * @param int
 * @return string
 */
function compliment($length){
    $wordList = '';
    while($length > 0){
        $comps = splitIt($length);
        foreach($comps as $vals){
            $word = getWord($vals,'');
            $wordList .= $word['lemma'].' ';
            $length -= $word['syllables'];
        }
        print PHP_EOL .' [ '.$length.' ] ';
    }
    return $wordList;
}

/**
 * Splits a number int random smaller chunks, ie 5 => 2,3 or 47 => 31,15,1
 * Returns an array of the smaller numbers.
 * @param int
 * @return array()
 */
function splitIt($length){
    print $length . ' : ';
    $splits = array();
    if(mt_rand(1,2) == 1 && $length > 1){
        $splits[1] = mt_rand(1, ($length - 1));
        $length -= $splits[1];
        $splits = array_merge($splits, splitIt($length));
    }else {
        $splits[] = $length;
    }
    return $splits;
}

/**
 * Returns a word of either character length or syllable length specified, within the class specified
 * @param int
 * @param string
 * @param bool
 * @return array(string, int)
 */
function getWord($length, $class, $syl = true) {
    global $db;
    
    $query = 'FROM `words`
              WHERE `types` LIKE "%'.$class.'%"';
    if(!$syl) {
        $query .= ' AND CHAR_LENGTH(`lemma`) <= '.$length;
    }else if($length >= 1) {
        $query .= ' AND `syllables` <= '.$length;
    }
    
    $result  = $db->query('SELECT COUNT(`lemma`) as words'. PHP_EOL .$query);
    $wn = $result->fetch_assoc();
    if($wn['words'] != 0){
        $query = 'SELECT `lemma`, `syllables`'. PHP_EOL .$query;
        $query .= ' LIMIT '.mt_rand(0, ($wn['words'] - 1)).',1';
        $result  = $db->query($query);
        $word = $result->fetch_assoc();
        return $word;
    }
    return '';
}
?>


