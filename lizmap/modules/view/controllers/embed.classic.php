<?php
/**
* Displays an embedded map based on one Qgis project.
* @package   lizmap
* @subpackage view
* @author    3liz
* @copyright 2011 3liz
* @link      http://3liz.com
* @license    Mozilla Public License : http://www.mozilla.org/MPL/
*/

include jApp::getModulePath('view').'controllers/lizMap.classic.php';

class embedCtrl extends lizMapCtrl {

    function index() {
        $req = jApp::coord()->request;
        $req->params['h'] = 0;
        $req->params['l'] = 0;

        $rep = parent::index();

        if ( $rep->getType() != 'html' )
            return $rep;

        // add embed specific css
        $bp = jApp::config()->urlengine['basePath'];
        $rep->addCSSLink($bp.'css/embed.css');
        $themePath = $bp.'themes/'.jApp::config()->theme.'/';
        $rep->addCSSLink($themePath.'css/embed.css');
        // force undisplay home
        $rep->addStyle('#mapmenu li.home','display:none;');
        // do not display locate by layer
        // display tooltip at bottom
        $jsCode = "
        $( document ).ready( function() {
          lizMap.events.on({
            'uicreated':function(evt){
              $('#mapmenu .nav-list > li > a').tooltip('destroy').tooltip({placement:'bottom'});
              var search = $('#nominatim-search');
              if ( search.length != 0 ) {
                $('#mapmenu').append(search);
                $('#nominatim-search div.dropdown-menu').removeClass('pull-right').addClass('pull-left');
              }
              $('#dock').css('top', ($('#mapmenu').height()+10)+'px');
              $('#content').addClass('embed');

              lizMap.updateContentSize();
              $('#mini-dock').css('top', $('#dock').css('top'));
              $('#sub-dock').css('top', $('#dock').css('top'));

              if ( $('#mapmenu li.locate').hasClass('active') )
                $('#button-locate').click();
              if ( $('#mapmenu li.switcher').hasClass('active') )
                $('#button-switcher').click();
              if ( $('#overview-toggle').hasClass('active') )
                $('#overview-toggle').click();
            },
            'minidockopened': function(evt) {
                if ( evt.id == 'locate' ) {
                  // autocompletion items for locatebylayer feature
                  $('div.locate-layer select').hide();
                  $('span.custom-combobox').show();
                }
            }
          });
        });
        ";
        $rep->addJSCode($jsCode);

        // Get repository key
        $repository = $this->repositoryKey;
        // Get the project key
        $project = $this->projectKey;

        $rep->body->assign('auth_url_return',
            jUrl::get('view~map:index',
                array(
                    "repository"=>$repository,
                    "project"=>$project,
                )
            )
        );

        return $rep;
  }

  protected function getProjectDockables() {
    $assign = parent::getProjectDockables();
    $available = array('switcher', 'metadata', 'locate', 'measure', 'tooltip-layer');//, 'print', 'permaLink'
    $dAssign = array();
    foreach ( $assign['dockable'] as $dock ) {
        if ( in_array( $dock->id, $available ) )
            $dAssign[] = $dock;
    }
    $assign['dockable'] = $dAssign;
    $mdAssign = array();
    foreach ( $assign['minidockable'] as $dock ) {
        if ( in_array( $dock->id, $available ) )
            $mdAssign[] = $dock;
    }
    $assign['minidockable'] = $mdAssign;
    $bdAssign = array();
    foreach ( $assign['bottomdockable'] as $dock ) {
        if ( in_array( $dock->id, $available ) )
            $bdAssign[] = $dock;
    }
    $assign['bottomdockable'] = $bdAssign;
    $rdAssign = array();
    foreach ( $assign['rightdockable'] as $dock ) {
        if ( in_array( $dock->id, $available ) )
            $rdAssign[] = $dock;
    }
    $assign['rightdockable'] = $rdAssign;
    return $assign;
  }
}