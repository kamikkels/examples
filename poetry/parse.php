<?php
/** 
 * Generic config parser, functions for any and all genericconf-type config files
 */
if ( !defined( 'LOADED_CONFIG_PARSER' ) ) {
    /**
     * Prevent loading of the config parser more than once
     */
    define( 'LOADED_CONFIG_PARSER', true );

    /**
     * Config key to look for to decide whether or not to include the value as a file, or to proceed as normal
     */
    define( 'INCLUDE_CFG', 'include_cfg' );

    /**
     * Email address to send critical configuration load errors to
     */
    define( 'CONFIG_WATCHER', 'KMR k@michalskis.org>' );

    /**
     * Global array to store configuration files that have already been included, to avoid infinite loops
     */
    $_included = array();


    /**
     * Function to load and parse a given config file into an array
     * This function acts recursively and will load any config file keyed by INCLUDE_CFG
     * An E_USER_ERROR level error will be generated for any files that either do not exist or cannot be read
     * An E_USER_WARNING level error will be generated for any files that have already been included
     * Invalid config lines will trigger an E_USER_ERROR level error
     * Attempted duplication of constants or invalid constant names will trigger E_USER_ERROR level errors
     * This function will also handle wildcard configuration files
     * Public function should be called by applications loading configuration files
     */
    function getConfig( $cfgFile ){
        global $_included;

        if( !is_array( $cfgFile ) ){
            $cfgFile = array( $cfgFile, );
        }

        foreach( $cfgFile AS $config ){
            if( strstr( $config, '*' ) ){
                foreach( glob( $config ) as $thisConfig ){
                    getConfig( $thisConfig );
                }
                continue;
            }

            if( is_file( $config ) === false ){
                _configError( $config . ' is not a file', E_USER_ERROR );
                return false;
            }

            if( is_readable( $config ) === false ){
                _configError( $config . ' is not readable', E_USER_ERROR );
                return false;
            }

            if( isConfigIncluded( $config ) ){
                _configError( $config . ' already included', E_USER_WARNING );
                return false;
            }
            
            // Add $cfgFile to the array of config files already included
            $_included[] = realpath( $config );

            // Read the configuration into an array
            $lines = file( $config );

            foreach( $lines AS $line ){
                // Skip blank or commented lines
                if( !strlen( trim( $line ) ) || preg_match( "/^\s*#(.*)/", $line ) ){
                    continue;
                }

                // Fatal on non blank / commented lines with no = sign
                if( !strstr( $line, '=' ) ){
                    _configError( $line . ' is an invalid config line', E_USER_ERROR );
                    return false;
                }

                // Extract config key and values from the current line
                list( $key, $value ) = preg_split( "/\s*=\s*/", trim( $line ), 2 );
                    
                // Handle boolean strings and quotes
                if( $value == 'true' ){
                    $value = true;
                }else if( $value == 'false' ){
                    $value = false;
                }else if( preg_match( '/^"(.*)"$/', $value, $matches ) ){
                    $value = $matches[1];
                }

                if( $key == INCLUDE_CFG ){
                    // Include additional config
                    getConfig( $value );
                }else if( defined( strtoupper( $key ) ) ){
                    if( constant( strtoupper( $key ) ) === $value ){
                        continue;
                    }
                    
                    /*
                     * Check to see if the current key is already defined and the value we are trying to assign is different to
                     * the existing value
                     */
                    _configError(
                        strtoupper( $key ) . ' already defined as ' . htmlspecialchars( var_export( constant( strtoupper( $key ) ), true ) ),
                        E_USER_ERROR
                    );
                    return false;
                }else{
                    // Check the config key matches the parameters we have defined as valid
                    if( !preg_match( "/^[A-Z][_A-Z]*[A-Z]$/i", $key ) ){
                        _configError( 'Invalid constant name ' . $key, E_USER_ERROR );
                        return false;
                    }
                    define( strtoupper( $key ), $value );
                }
            }
        }
        return true;
    }
    /**
     * Function to check if a given file has already been included
     */
    function isConfigIncluded( $cfgFile ){
        global $_included;
        return in_array( realpath( $cfgFile ), $_included );
    }
    /**
     * Function to handle configuration errors
     */
    function _configError( $text, $level = null, $watcher = CONFIG_WATCHER ){
        mail(
            $watcher,
            'Configuration Error',
            "A configuration error has been encountered on {$_SERVER['SERVER_NAME']}\n\n{$text}",
            'From: Configuration Watcher <no.reply@kamikkels.net>'
        );

        if( !is_null( $level ) ){
            trigger_error( $text, $level );
        }
        return;
    }
}
