<?php
class Content
{   
    static $ignore = array();
    
    public static function getLinksFromContent( $data, $url )
    {
        $html = str_get_html( $data );
        if ( !is_object( $html ) )
            return false;

        $searchString = 'a[href]';
        $foundLinks = $html->find( $searchString );

        $links = self::prepareContentLinks( $foundLinks, $url );

        $html->clear();
        unset( $html );

        return ( count( $links ) ) ? $links : false;
    } //end func
    
    public static function prepareContentLinks( $rawLinks, $currentUrl )
    {
        $links = array();

        if ( count( $rawLinks ) )
        {
            foreach ( $rawLinks as $a )
            {
                if ( self::isIgnore( $a->href ) || $a->href == '#' || $a->href == '' || $a->href == ' ' ) continue;
                $protocol = ( strpos( $a->href, Http::$protocol[0] ) === 0 )? Http::$protocol[0] : false;
				if ( !$protocol )
					$protocol = ( strpos( $a->href, Http::$protocol[1] ) === 0 )? Http::$protocol[1] : false;
                $doesLinkContainDomain = ( strpos( $a->href, Http::$domain ) !== false );
                if ( $protocol && $doesLinkContainDomain )
                {
                    array_push( $links, $a->href );
                }

                if ( ! $protocol )
                {
                    if ( substr( $a->href, 0, 1 ) == '/' )
					{
						$link = Http::$currProtocol . Http::$domain . $a->href;
					}
					else
					{
                        $link = Http::GetPathFromUrl( $currentUrl ) . $a->href;
					}
                    array_push( $links, $link );
                }
            }
        }

        return $links;
    }//end func
    
    public static function loadIgnore()
    {
        self::$ignore = Fs::getArrayFromFile(IGNORE_FILE );
        return true;
    }
    
    public static function isIgnore( $url )
    {
        foreach( self::$ignore as $item )
        {
            if ( preg_match('/' . $item . '/', $url) == 1 ) return true;
        }
        return false;
    } 
}//end class