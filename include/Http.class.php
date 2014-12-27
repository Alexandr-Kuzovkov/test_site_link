<?php

class Http
{
    
    /**
     * @var const string LOG_FILE Log file
     */
    const LOG_FILE = 'log/http.txt';
    
    /**
     * @var const string COOKIE_FILE Cookie file
     */
    const COOKIE_FILE = 'cookie.txt';
    
    /**
     * @var const string USER_AGENT User-Agent string
     */
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36';

    /**
     * @var $codes array of good codes
     */
    static $codes = array( 200, 201, 301 );
    
    /**
     * @var $domain string domain of site
     */
    static $domain = '';
	
	/**
     * @var $protocol array of string protocols
     */
	static $protocol = array('http://','https://');
	
	/**
     * @var $currProtocol string protocol of tested site
     */
	static $currProtocol = '';
    
    /**
     * @var $contenType string response header Content-Type 
     */
    static $contenType = '';
    
    /**
     * function GetUrlFromFile read url from text file
     * @param string $filename Full path to file
     * @return string $url Site url
     */
    public static function GetUrlFromFile( $filename )
    {
        $url = false;

        if ( file_exists( $filename ) )
        {
            $url = file_get_contents( $filename );
        }

        return $url;
    } //end func
    
    
    /**
     * GetNameFromUrl extract domain name from url
     * @param string $url Url
     * @return string $url Domen name
     */
    public static function GetNameFromUrl( $url )
    {
        if ( strpos( $url, self::$protocol[0] ) === 0 )
        {
            $domain = str_replace( self::$protocol[0], '', $url );
        }
		
		if ( strpos( $url, self::$protocol[1] ) === 0 )
        {
            $domain = str_replace( self::$protocol[1], '', $url );
        }

        if ( strpos( $domain, '/' ) !== false )
        {
            $domain = substr( $domain, 0, strpos( $domain, '/' ) );
        }

        return $domain;
    }
	
	/**
	* Set protocol for current tested site
	* $url string URL of tested site
	**/
	public static function setCurrProtocol($url)
	{
		 if ( strpos( $url, self::$protocol[0] ) === 0 )
        {
            self::$currProtocol = self::$protocol[0];
        }
		
		if ( strpos( $url, self::$protocol[1] ) === 0 )
        {
             self::$currProtocol = self::$protocol[1];
        }
	}

    /**
     * function GetContentFromUrl get content as string from given url
     * @param string $url Url
     * @return string $content | false Content or false if fail
     */
    public static function GetContentFromUrl( $url )
    {
        $result     = false;
        $curlHandle = curl_init( $url );
        if ( $curlHandle === false )
        {
            return false;
        }

        $logFile = @fopen( self::LOG_FILE, 'a+' );

        curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );

        if ( $logFile )
        {
            curl_setopt( $curlHandle, CURLOPT_STDERR, $logFile );
        }

        $content = curl_exec( $curlHandle );
        $info    = curl_getinfo( $curlHandle );
        $error   = curl_error( $curlHandle );

        
        if ( class_exists( 'Logger' ) )
        {
            $logInfo = array( 
                            $info['url'], 
                            $info['total_time'], 
                            $info['download_content_length'], 
                            $error
                        );
            Logger::write( join( ', ', $logInfo ) );
        }
        
        if ( ! curl_errno( $curlHandle ) )
        {
            $result = true;
        }

        if ( $logFile )
        {
            fclose( $logFile );
        }
        curl_close( $curlHandle );

        return ( $result ) ? $content : false;
    } //end func

    /**
     * function GetPathFromUrl extract path from url
     * @param string $url Path in Url
     * @return string $path path from Url
     */
    public static function GetPathFromUrl( $url )
    {
        $protocol = '';
		if ( strpos( $url, self::$protocol[0] ) === 0 )
        {
            $path = str_replace( self::$protocol[0], '', $url );
			$protocol = self::$protocol[0];
        }
		
		if ( strpos( $url, self::$protocol[1] ) === 0 )
        {
            $path = str_replace( self::$protocol[1], '', $url );
			$protocol = self::$protocol[1];
        }

        if ( strpos( $path, '/' ) === false )
        {
            $path = $protocol . $path . '/';
        }
        else
        {
            $path = $protocol . substr( $path, 0, strrpos( $path, '/' ) ) . '/';
        }

        return $path;
    } //end func
    
    
    /**
     * check is link internal
     * @param string $url  Url
     * @return bool true if link internal or false
     */
    public static function isLinkInternal( $url )
    {
        return ( self::GetNameFromUrl( $url ) == self::$domain ) ? true : false;
        
    }//end func
    
    
     /**
     * function ConvertArrayToPostString Converting
     *  array contains POST
     *  data to POST string
     * in rawurlencode format
     * @param array $array POST param array
     * @return string $post POST string param
     */
     public static function ConvertArrayToPostString( $array )
     {
        $post = '';
        $len = count( $array );
        if ( is_array( $array ) && $len )
        {
            $count = 0;
            foreach( $array as $key  => $value )
            {
                $post .= trim( $key ) . '=' . rawurlencode( trim( $value ) );
                $count++;
                if( $count < $len ) $post .= '&'; 
            }
        }
        return $post;
     } //end func
     
     
     /**
     * function SendRequest send Http request
     * @param string $url Url
     * @param boolean $typePost GET request or POST request
     * @param  array $headers Array of headers string ( "Name: value" ) 
     * @param  string $post POST-data string
     * @param  string $fileCookie File for save cookie
     * @return string $content | false Content or false if fail
     */
     public static function SendRequest( $url, $typePost = false, $headers = array(), $post = 'op=0', $referer = '' )
     {
        $result     = false;
        $curlHandle = curl_init( $url );
        if ( $curlHandle === false )
        {
            return false;
        }

        $logFile = @fopen( self::LOG_FILE, 'a+' );

        curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $curlHandle, CURLOPT_COOKIEJAR, trim(self::COOKIE_FILE) );
        curl_setopt( $curlHandle, CURLOPT_COOKIEFILE, trim( self::COOKIE_FILE) );
        curl_setopt( $curlHandle, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $curlHandle, CURLOPT_USERAGENT, self::USER_AGENT );
        if ( $referer != '' )
            curl_setopt( $curlHandle, CURLOPT_REFERER, $referer );
        if ( $typePost )
        {
            curl_setopt( $curlHandle, CURLOPT_POST, 1 );
            curl_setopt( $curlHandle, CURLOPT_POSTFIELDS, $post );
        }
        if ( $logFile )
        {
            curl_setopt( $curlHandle, CURLOPT_STDERR, $logFile );
        }

        $content = curl_exec( $curlHandle );
        $info    = curl_getinfo( $curlHandle );
        if ( isset( $info['content_type'] ) ) self::$contenType = $info['content_type'];
        $error   = curl_error( $curlHandle );

        if ( class_exists( 'Logger' ) )
        {
            $logInfo = array( 
                            $info['url'], 
                            $info['total_time'], 
                            $info['download_content_length'], 
                            $error
                        );
            Logger::write( join( ', ', $logInfo ) );
        }

        if ( ! curl_errno( $curlHandle ) )
        {
            $result = true;
        }

        if ( $logFile )
        {
            fclose( $logFile );
        }
        curl_close( $curlHandle );

        return ( $result ) ? $content : false;
     }//end func
     
     /**
     * function ClearSession Clear cookie file
     */
     public static function ClearSession()
     {
        if( $f = @fopen( self::COOKIE_FILE, "w" ) )
        {
            fclose( $f );
        }
        
     }//end func
     
     /**
     * function UploadFile Upload file to server
     * @param string $url Url
     * @param string $filename Name of file to upload
     * @param string $filefield Name of field file
     * @param  array $headers Array of headers string ( "Name: value" )
     * @param  array $post POST-data array
     * @param  string $fileCookie File for save cookie
     * @return string $content | false Content or false if fail
     */
     public static function UploadFile( $url, $filename, $filefield, $headers = array(), $post = array(), $referer = '' )
     {
        $result     = false;
        if ( ! file_exists( $filename ) ) return false;
        
        $curlHandle = curl_init( $url );
        if ( $curlHandle === false )
        {
            return false;
        }

        $logFile = @fopen( self::LOG_FILE, 'a+' );
        $post[$filefield] = '@' . $filename;
        curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $curlHandle, CURLOPT_COOKIEJAR, trim(self::COOKIE_FILE) );
        curl_setopt( $curlHandle, CURLOPT_COOKIEFILE, trim( self::COOKIE_FILE) );
        curl_setopt( $curlHandle, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $curlHandle, CURLOPT_USERAGENT, self::USER_AGENT );
        if ( $referer != '' )
        {
            curl_setopt( $curlHandle, CURLOPT_REFERER, $referer );
        }

        //curl_setopt( $curlHandle, CURLOPT_UPLOAD, 0 );
        //curl_setopt( $curlHandle, CURLOPT_PUT, 0 );
        curl_setopt( $curlHandle, CURLOPT_POST, 1 );
        //curl_setopt( $curlHandle, CURLOPT_INFILE, $file );
        //curl_setopt( $curlHandle, CURLOPT_INFILESIZE, filesize( trim( $filename ) ) );            
        curl_setopt( $curlHandle, CURLOPT_POSTFIELDS, $post );
        
        if ( $logFile )
        {
            curl_setopt( $curlHandle, CURLOPT_STDERR, $logFile );
        }

        $content = curl_exec( $curlHandle );
        $info    = curl_getinfo( $curlHandle );
        $error   = curl_error( $curlHandle );

        if ( class_exists( 'Logger' ) )
        {
            $logInfo = array( 
                            $info['url'], 
                            $info['total_time'], 
                            $info['download_content_length'], 
                            $error
                        );
            Logger::write( join( ', ', $logInfo ) );
        }

        if ( ! curl_errno( $curlHandle ) )
        {
            $result = true;
        }

        if ( $logFile )
        {
            fclose( $logFile );
        }
        curl_close( $curlHandle );
        return ( $result ) ? $content : false;
     }//end func
     
	public static function checkLink( $url )
    {
        $curlHandle = curl_init( $url );
        if ( $curlHandle === false )
        {
            return false;
        }
        curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $curlHandle, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $curlHandle, CURLOPT_HEADER , 1 );
        curl_setopt( $curlHandle, CURLOPT_NOBODY , 1 );
        curl_exec( $curlHandle );
        $code = curl_getinfo( $curlHandle, CURLINFO_HTTP_CODE );
        return ( in_array( $code, self::$codes ) )? true : false;
    }
    
    public static function lastWasImage()
    {
        return ( preg_match('/^image.*?/',self::$contenType) == 1 )? true : false;
    }
	
} //end class
