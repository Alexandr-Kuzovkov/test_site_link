<?php
    ini_set( 'include_path', './include' );
    require_once ( 'common.inc.php' );
    
    $url = (isset( $argv[1] ))? $argv[1] : false;
    if ( !$url )
    {
        echo ( 'Usage: php ' . $argv[0] . ' <URL>' );
        exit();
    }
     
    Queue::reset();
    Content::loadIgnore();
    
    if ( class_exists( 'Logger' ) ) Logger::clear();
    
    if ( ! Http::checkLink( $url ) ) 
    {
        Queue::pushUrlToFailLink( $url, '' );
        echo "\nURL fail. Done";
    }
    else
    {
        Http::$domain = Http::GetNameFromUrl( $url );
		Http::setCurrProtocol( $url );
        Queue::pushUrlToOkLink( $url, '' );
        Queue::pushUrlToQueue( $url );
        
        while ( ! Queue::isQueueEmpty() )
        {
            $url = Queue::popUrlFromQueue();
            echo "\nProcessing URL: " . $url;
            $content = Http::SendRequest( $url );
            if ( $content )
            {
                Queue::pushUrlToProcessedLink( $url );
                $links = array();
                if ( !Http::lastWasImage() )
                {
                    $links = Content::getLinksFromContent( $content, $url );
                }
                if ( is_array( $links ) && count( $links ))
                {
                    foreach ( $links as $link )
                    {
                        if ( Http::checkLink( $link ) )
                        {
                            Queue::pushUrlToOkLink( $url, $link );
                            if ( Http::isLinkInternal( $link ) && ! Queue::isUrlProcessed($link))
                                Queue::pushUrlToQueue( $link );
                        }
                        else
                        {
                            Queue::pushUrlToFailLink( $url, $link );
                        }
                    }
                }
            }
            else
            {
                echo "\nFail get content from " . $url;
                Queue::removeUrlFromQueue( $url );
            }
            echo ' - complete';
            Queue::removeUrlFromQueue( $url );
        }
        echo "\nDone";
    }
    
    
