<?php

class Logger
{
    public static function write( $data, $filename = FILE_LOG )
    {
        $date = date( DATE_OUTPUT_FORMAT, time() );
        $f = @fopen( $filename, "a+" );
        if( $f )
        {
            fwrite( $f, $date . ": " . $data . "\n" );
            fclose( $f );
        }
        
    }//end func

    public static function clear( $filename = FILE_LOG )
    {
        $f = @fopen( $filename, "w" );
        if ( $f ) 
        {
            fclose( $f );
        }  
    }//end func

} //end class
