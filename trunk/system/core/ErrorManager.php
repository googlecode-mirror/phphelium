<?php
namespace Helium;

/*
 * ErrorManager.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all error behavior
 */

class ErrorManager extends \Exception {
    protected $severity;
    private $logger;
    
    public function __construct($message,$code=false,$severity=false,$filename=false,$lineno=false) {
        if (empty($code)) $code = E_USER_ERROR;
        
        $this->message = $message;
        $this->code = $code;
        $this->severity = $severity;
        $this->file = $filename;
        $this->line = $lineno;

        $this->logger = new Log();

        if (DISPLAY_ERRORS) {
            echo '<div style="font-size:1.4em;"><strong>Error:</strong> '.$this->message.'</div>';
            echo '<div>Code: '.$this->code.' | Severity: '.$this->severity.' | File: '.$this->file.' | Line: '.$this->line.'</div>';

            echo '<pre>';
            echo $this->getTraceAsString();
            echo '</pre>';

            exit();
        }
    }

    /**
     *
     * function: getSeverity
     * Get error severity
     * @access public
     * @return string
     */
    public function getSeverity() {
        return $this->severity;
    }

    /**
     *
     * function: fatal
     * Send fatal error
     * @access public
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return string
     */
    public function fatal($errno,$errstr,$errfile,$errline) {
        $this->logger->error($errno.' | '.$errstr);
    }

    /**
     *
     * function: warn
     * Send warning error
     * @access public
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return string
     */
    public function warn($errno,$errstr,$errfile,$errline) {
        $this->logger->warning($errno.' | '.$errstr);
    }
}

