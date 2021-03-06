<?php

/**
* Return list of questions for poll
* @return string
* @param string $sFile
* @param int    $iPoll
* @param bool   $bResult
* @param string $sOption
*/
function throwPollAnswers( $sFile, $iPoll, $bResult = null, $sOption = null, $iWidth = 150 ){
  $oTpl   =& TplParser::getInstance( );
  //{ epesi

  if( isset( $bResult ) )
    $sBlock = 'ANSWERS_RESULT_';
  else
    $sBlock = 'ANSWERS_FORM_';

  $ret = DB::GetAll('SELECT id as iAnswer, f_answer as sAnswer, f_votes as iCountAnswers, f_poll as iPoll FROM premium_ecommerce_poll_answers_data_1 WHERE f_poll=%d',array($iPoll));
  if( isset( $bResult ) ){
    $iSummary = 0;
    foreach( $ret as $r ){
        $iSummary += $r['iCountAnswers'];
    }
  }

  $content = null;
  foreach( $ret as $r ){
    if(!isset($r['iCountAnswers']))
      $r['iCountAnswers'] = 0;
    if( isset( $bResult )){
      if( $r['iCountAnswers'] > 0 && $iSummary > 0 )
        $r['iPercentage'] = round( $r['iCountAnswers'] / $iSummary * 100 );
      else
        $r['iPercentage'] = 0;

      $r['iWidth'] = round( $r['iPercentage'] * $iWidth / 100 );
    }
    $oTpl->setVariables( 'aList', $r );
    $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
  } // end for
    //} epesi
  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, $sBlock.'HEAD' ).$content.$oTpl->tbHtml( $sFile, $sBlock.'FOOT' );
  return $content;
} // end function throwPollAnswers


/**
* Return list of questions for poll
* @return string
* @param string $sFile
*/
function throwPollQuestions( $iPoll = null ){
    //{ epesi
    if( !isset( $iPoll ) )
	return DB::GetRow('SELECT id as iPoll, f_question as sQuestions FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' ORDER BY f_position DESC LIMIT 1');
    return DB::GetRow('SELECT id as iPoll, f_question as sQuestions FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' AND id=%d',array($iPoll));
    //} epesi
} // end function throwPollQuestions


/**
* Returns poll content
* @return string
* @param string $sFile
* @param int $iPoll
* @param string $sOption
*/
function throwPoll( $sFile, $iPoll = null, $sOption = null, $iWidth = 150 ){
  $oTpl   =& TplParser::getInstance( );

  if( isset( $iPoll ) && $iPoll == 0 )
    $aData = throwPollQuestions( null );
  elseif( isset( $iPoll ) )
    $aData = throwPollQuestions( $iPoll );

    //{ epesi
    if( isset( $aData['iPoll'] ) ){
      if( isset( $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$aData['iPool']] ) )
        $bResult = true;
      else
        $bResult = null;
      $aData['sAnswers'] = throwPollAnswers( $sFile, $aData['iPoll'], $bResult, $sOption, $iWidth );
      $oTpl->setVariables( 'aData', $aData );
      return $oTpl->tbHtml( $sFile, 'POLL' );
    }
    else
      return $oTpl->tbHtml( $sFile, 'NOT_EXIST' );
    //} epesi

} // end function throwPoll

/**
* Saves user selected answer
* @param int $iAnswer
*/
function dbSavePollAnswer( $iAnswer, $iPoll ){
  //{ epesi
  if( !isset( $iPoll ) || $iPoll == 0 )
    $iPoll = DB::GetOne('SELECT id FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' ORDER BY f_position DESC LIMIT 1');
  if( !isset( $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$iPoll] ) ){
    DB::Execute('UPDATE premium_ecommerce_poll_answers_data_1 SET f_votes=f_votes+1 WHERE id=%d AND f_poll=%d',array($iAnswer,$iPoll));
    $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName] = $iAnswer;
    setCookie( LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName, $iAnswer, time( ) + 2592000 );
  }
  //} epesi
} // end function dbSavePollAnswer

?>