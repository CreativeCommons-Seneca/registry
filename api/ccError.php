<?php

class ccError {
    static $ERR_API_BASE 			        = 3000;
    static $ERR_API_INVALID_PARAM           = 3001;
    static $ERR_API_INVALID_MATCH_PARAM     = 3002;
    static $ERR_API_INVALID_DELETE_PARAM    = 3003;
    static $ERR_API_INVALID_ADD_PARAM       = 3004;    

    static $ERR_API_SOCKET_CREATE           = 3005;
    static $ERR_API_SOCKET_CONNECT          = 3006;
    static $ERR_API_SOCKET_WRITE            = 3007;
    static $ERR_API_SOCKET_READ             = 3008;

    static $ERR_API_DB_CONNECT              = 3010;
    static $ERR_API_DB_QUERY                = 3011;

}

function getErrorString($code){
    $rString = "";

    switch($code){
        case ccError::$ERR_API_INVALID_PARAM:
            $rString = "Invalid request parameters: request must be add, match or delete";
            break;
        case ccError::$ERR_API_INVALID_MATCH_PARAM:
            $rString = "Invalid request parameters: match must have hash";
            break;
        case ccError::$ERR_API_INVALID_DELETE_PARAM:
            $rString = "Invalid request parameters: delete must have id";
            break;
        case ccError::$ERR_API_INVALID_ADD_PARAM:
            $rString = "Invalid request parameters: ADD";
            break;
        case ccError::$ERR_API_SOCKET_CREATE:
            $rString = "Socket Creation Failed";
            break;
        case ccError::$ERR_API_SOCKET_CONNECT:
            $rString = "Socket Connection Failed";
            break;
        case ccError::$ERR_API_SOCKET_WRITE:
            $rString = "Socket Writing Failed";
            break;
        case ccError::$ERR_API_SOCKET_READ:
            $rString = "Socket Reading Failed";
            break;
        case ccError::$ERR_API_DB_CONNECT:
            $rString = "Cannot connect database";
            break;
        case ccError::$ERR_API_DB_QUERY:
            $rString = "Database query error";
            break;

        default:
            $rString = "Not Defined";
            break;
    }
    return $rString;
}

?>