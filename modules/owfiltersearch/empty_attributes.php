<?php

$Module = $Params["Module"];
if( is_callable( 'eZTemplate::factory' ) ) {
    $tpl = eZTemplate::factory( );
} else {
    include_once ('kernel/common/template.php');
    $tpl = templateInit( );
}

$filterParams = array( );
if( $Module->currentAction( ) != FALSE ) {
    foreach( $Module->Functions[$Module->currentView()]['post_action_parameters'][$Module->currentAction()] as $parameter ) {
        if( !empty( $Params[$parameter] ) ) {
            $filterParams[$parameter] = $Params[$parameter];
        } elseif( $Module->hasActionParameter( $parameter ) ) {
            $filterParams[$parameter] = $Module->actionParameter( $parameter );
        }
    }
}

$tpl->setVariable( 'empty_attribute_filter_type', 'AND' );
$tpl->setVariable( 'filled_attribute_filter_type', 'AND' );
$tpl->setVariable( 'translation_filter', '' );

foreach( $filterParams as $variableName => $value ) {
    $variableName = strtolower( preg_replace( '/\B([A-Z])/', '_$1', $variableName ) );
    $tpl->setVariable( $variableName, $value );
}

// if we know the content class, fill the list of its attributes
if( isset( $filterParams['ClassFilter'] ) ) {
    $contentClass = eZContentClass::fetchByIdentifier( $filterParams['ClassFilter'] );
    if( $contentClass instanceof eZContentClass ) {
        $tpl->setVariable( 'class_attribute_list', $contentClass->fetchAttributes( ) );
    }
}

if( !empty( $filterParams ) && isset( $filterParams['ClassFilter'] ) && (isset( $filterParams['EmptyAttributeFilters'] ) || isset( $filterParams['FilledAttributeFilters'] )) ) {
    $filter = new OWFilterSearchEmptyAttributes( $filterParams );
    $offset = $Params['Offset'];
    if( !is_numeric( $offset ) ) {
        $offset = 0;
    }

    $maxElementByPage = array(
        10,
        10,
        25,
        50
    );
    $length = $maxElementByPage[min( array(
        eZPreferences::value( 'owfiltersearch_empty_attributes_limit' ),
        3
    ) )];
    $results = $filter->getResults( array(
        'offset' => $offset,
        'length' => $length
    ) );
    $tpl->setVariable( 'limit', $length );
    $tpl->setVariable( 'results', $results['nodes'] );
    $tpl->setVariable( 'result_count', $results['count'] );
    $filterParamURIArray = array( );
    foreach( $filterParams as $key => $value ) {
        $filterParamURIArray[$key] = is_array( $value ) ? implode( ',', $value ) : $value;
    }
    $page_uri = trim( $Module->redirectionURI( 'owfiltersearch', 'empty_attributes', $filterParamURIArray ), '/' );
    $tpl->setVariable( 'page_uri', $page_uri );
    $tpl->setVariable( 'view_parameters', array( 'offset' => $offset ) );
}

$Result['content'] = $tpl->fetch( 'design:owfiltersearch/empty_attributes.tpl' );
$Result['left_menu'] = 'design:owfiltersearch/menu.tpl';

if( function_exists( 'ezi18n' ) ) {
    $Result['path'] = array(
        array( 'text' => ezi18n( 'design/admin/parts/owfiltersearch/menu', 'Filter search' ) ),
        array(
            'url' => 'filtersearch/empty_attributes',
            'text' => ezi18n( 'design/admin/parts/owfiltersearch/menu', 'Empty attributes' )
        )
    );

} else {
    $Result['path'] = array(
        array( 'text' => ezpI18n::tr( 'design/admin/parts/owfiltersearch/menu', 'Filter search' ) ),
        array(
            'url' => 'filtersearch/list',
            'text' => ezpI18n::tr( 'design/admin/parts/owfiltersearch/menu', 'Empty attributes' )
        )
    );

}
