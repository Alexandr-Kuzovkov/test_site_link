<?php

class Queue
{
    
    public static function popUrlFromQueue()
    {
        //возвращает еще не обработанный URL
        return Fs::getsFromFile( FILE_QUEUE );
    }//end func

    public static function removeUrlFromQueue($url)
    {
        //удаляет URL из очереди
        return Fs::removeItemFromFile( FILE_QUEUE, $url );
    }//end func
    
    public static function popAllFromQueue()
    {
        //возвращает массив еще не обработанных URL
        return Fs::getArrayFromFile( FILE_QUEUE );
    }//end func

    public static function pushUrlToQueue( $url )
    {
        return Fs::appendToFileUnique( FILE_QUEUE, $url );
    }//end func

    public static function pushArrayToQueue( $urls )
    {
        return Fs::appendArrayIntoFileUnique( FILE_QUEUE, $urls );
    }//end func

    public static function isQueueEmpty()
    {
        return Fs::isFileEmpty( FILE_QUEUE );
    }//end func
    
    public static function pushUrlToOkLink( $page, $url )
    {
        return Fs::appendToFileUnique( FILE_OK_LINKS, $page . ': ' . $url );
    }//end func
    
    public static function pushUrlToFailLink( $page, $url )
    {
        return Fs::appendToFileUnique( FILE_FAIL_LINKS, $page . ': ' . $url );
    }//end func
    
    public static function pushUrlToProcessedLink( $url )
    {
        return Fs::appendToFileUnique( FILE_PROCESSED_LINKS, $url );
    }//end func
    
    public static function isUrlProcessed( $url )
    {
        return Fs::hasFileItem( FILE_PROCESSED_LINKS, $url );
    }//end func
    
    public static function reset()
    {
        Fs::clearFile( FILE_QUEUE );
        Fs::clearFile( FILE_OK_LINKS );
        Fs::clearFile( FILE_FAIL_LINKS );
        Fs::clearFile( FILE_PROCESSED_LINKS );
    }//end func
    
    
}//end class
