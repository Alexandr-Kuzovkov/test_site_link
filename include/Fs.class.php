<?php

class Fs
{
    const MAX_BUF_LEN = 4096;
    const COUNT_BUF_LEN = 30;

    public static function removeDir( $dir )
    {
        if ( is_dir( $dir ) )
        {
            $d = opendir( $dir );
            while ( $name = readdir( $d ) )
            {
                if ( $name == '.' or $name == '..' )
                    continue;
                unlink( $dir . '/' . $name );
            }
            closedir( $d );
            return rmdir( $dir );
        }
    } //end func

    public static function prepareDir( $dirName )
    {
        $dirName = DATA_PATH . '/' . $dirName;
        if ( file_exists( $dirName ) ) //if dir exists remove it
        {
            if ( is_dir( $dirName ) )
            {
                self::removeDir( $dirName );
            }
        }
        return mkdir( $dirName );
    } //end func

    public static function appendToFile( $filename, $data )
    {
        $fileHandle = @fopen( $filename, 'a+' );
        if ( $fileHandle )
        {
            flock( $fileHandle, LOCK_EX );
            $result = fwrite( $fileHandle, $data . "\n" );
            flock( $fileHandle, LOCK_UN );
            fclose( $fileHandle );
            return $result;
        }
        return false;
        
    } //end func
    
    public static function clearFile( $filename )
    {
        if (!file_exists($filename)) return false;
        $fileHandle = @fopen( $filename, 'w' );
        if ( $fileHandle )
        {
            fclose( $fileHandle );
            return true;
        }
        return false;
    } //end func

    public static function appendArrayIntoFile( $filename, $data )
    {
        $count = 0;
        $fileHandle = @fopen( $filename, 'a+' );
        if ( $fileHandle )
        {
            flock( $fileHandle, LOCK_EX );
            foreach ( $data as $item )
            {
                fputs( $fileHandle, $data . "\n" );
                $count++;
            }
    
            flock( $fileHandle, LOCK_UN );
            fclose( $fileHandle );
            return $count;
        }
        return false;
        
    } //end func

    public static function putArrayIntoFile( $filename, $data )
    {
        $count = 1;
        $fileHandle = @fopen( $filename, 'w' );
        if ( $fileHandle )
        {
             flock( $fileHandle, LOCK_EX );
            foreach ( $data as $item )
            {
                fputs( $fileHandle, $item . "\n" );
                $count++;
            }
    
            flock( $fileHandle, LOCK_UN );
            fclose( $fileHandle );
            return $count;
        }
        return false;
       
    } //end func

    public static function getsFromFile( $filename )
    {
        if ( file_exists( $filename ) )
        {
            $fileHandle = fopen( $filename, 'r' );
            if ( $fileHandle )
            {
                flock( $fileHandle, LOCK_EX );
                $result = rtrim( fgets( $fileHandle, self::MAX_BUF_LEN ) );
                flock( $fileHandle, LOCK_UN );
                fclose( $fileHandle );
                return $result;
            }
            return false;          
        }
        return false;
    }

    public static function getArrayFromFile( $filename )
    {
        if ( file_exists( $filename ) )
        {
            $result = array();
            $fileHandle = fopen( $filename, 'r' );
            if ( $fileHandle )
            {
                flock( $fileHandle, LOCK_EX );
                while ( ! feof( $fileHandle ) )
                {
                    $item = rtrim( fgets( $fileHandle, self::MAX_BUF_LEN ) );
                    if ( $item != '' )
                        $result[] = $item;
                }
                flock( $fileHandle, LOCK_UN );
                fclose( $fileHandle );
                return $result;
            }
            return false;        
        }
        return false;
    }

    public static function removeItemFromFile( $filename, $item )
    {
        if ( file_exists( $filename ) )
        {
            $items = self::getArrayFromFile( $filename );
            array_splice( $items, array_search( $item, $items ), 1 );
            return self::putArrayIntoFile( $filename, $items );
        }
        return false;

    }
    
    public static function hasFileItem( $filename, $item )
    {
        if ( file_exists( $filename ) )
        {
            $items = self::getArrayFromFile( $filename );
            if ( in_array( $item, $items ) ) return true;
        }
        return false;
    }

    public static function appendToFileUnique( $filename, $data )
    {
        if ( file_exists( $filename ) )
        {
            $items = self::getArrayFromFile( $filename );
            if ( array_search( $data, $items ) === false )
                return self::appendToFile( $filename, $data );
            return true;
        }
        else
        {
            return self::appendToFile( $filename, $data );
        }

    }

    public static function appendArrayIntoFileUnique( $filename, $data )
    {
        if ( file_exists( $filename ) )
        {
            $items = self::getArrayFromFile( $filename );

            foreach ( $data as $item )
            {
                if ( in_array( $item, $items ) === false )
                    $items[] = $item;
            }
            return self::putArrayIntoFile( $filename, $items );
        }
        else
        {
            return self::appendArrayIntoFile( $filename, $data );
        }

    } //end func


    public static function isFileEmpty( $filename )
    {
        if ( file_exists( $filename ) )
        {
            $result = self::getArrayFromFile( $filename );
            if ( count( $result ) )
                return false;
            return true;
        }
        return true;
    } //end func

    public static function putContentToFile( $content )
    {
        $contentOk = ( is_string( $content ) ) && ( $content != '' );
        if ( $contentOk )
        {
            $startUrl = Http::getUrlFromFile( FILE_URL );
            $dir = Http::nameFromUrl( $startUrl );
            $dir = DATA_PATH . '/' . $dir;
            $filename = 'content';
            $result = file_put_contents( $dir . '/' . $filename . '.html', $content );
            $logString = $result . ' bytes saved into file ' . $filename . '.html';
            Logger::write( $logString );
            return $result;
        }
        else
        {
            return false;
        }
    } //end func

    public static function getCount( $filename )
    {
        if ( file_exists( $filename ) )
        {
            $fileHandle = @fopen( $filename, 'r' );
            if ( $fileHandle )
            {
                flock( $fileHandle, LOCK_EX );
                $count = intval( rtrim( fgets( $fileHandle, self::COUNT_BUF_LEN ) ) );
                flock( $fileHandle, LOCK_UN );
                fclose( $fileHandle );
                return $count;
            } 
        }
        return false;
    } //end func

    public static function setCount( $filename, $count )
    {
        $fileHandle = @fopen( $filename, 'w' );
        if ( $fileHandle )
        {
            flock( $fileHandle, LOCK_EX );
            fputs( $fileHandle, strval( $count ) );
            flock( $fileHandle, LOCK_UN );
            fclose( $fileHandle );
            return $count;
        }
        return false; 
    } //end func
} //end class
