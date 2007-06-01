<?php
require_once 'simpletest/reporter.php';
/**
 * Simple reporter for tests run within the testrig.
 * @package midcom.tests
 * Based on the simpletest package.
 * 
 */

    class midcom_tests_reporter extends HTMLReporter {
        
        /**
         *    Does nothing yet. The first output will
         *    be sent on the first test start. For use
         *    by a web browser.
         *    @access public
         */
        function midcom_tests_reporter() {
            $this->SimpleReporter();
        }
        
        /**
         *    Paints the staring header (i.e. the testname )
         *    @param string $test_name      Name class of test.
         *    @access public
         */
        function paintHeader($test_name) {
            /* add the needed css to the header. */
            
            print "<div style='padding:1em;width:60em;' >";
            print "<h1>$test_name</h1>\n<p>\n";
            flush();
        }
        
        /**
         *    Paints the CSS. Add additional styles here.
         *    @return string            CSS code as text.
         *    @access protected
         */
        function _getCss() {
            return ".fail { color: red; display:block; } pre { background-color: lightgray; }";
        }
        
        /**
         *    Paints the end of the test with a summary of
         *    the passes and failures.
         *    @param string $test_name        Name class of test.
         *    @access public
         */
        function paintFooter($test_name) {
            $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
            print "<div style=\"";
            print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
            print "\">";
            print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
            print " test cases complete:\n";
            print "<strong>" . $this->getPassCount() . "</strong> passes, ";
            print "<strong>" . $this->getFailCount() . "</strong> fails and ";
            print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
            print "</div>\n";
            print "</div>";
        }
        
        /**
         *    Paints the test failure with a breadcrumbs
         *    trail of the nesting test suites below the
         *    top level test.
         *    @param string $message    Failure message displayed in
         *                              the context of the other tests.
         *    @access public
         */
        function paintFail($message) {
            
            parent::paintFail($message);
            print "<br/><span class=\"fail\">Fail</span>: <br/>";
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
        }
        
        /**
         *    Paints a PHP error or exception.
         *    @param string $message        Message is ignored.
         *    @access public
         *    @abstract
         */
        function paintException($message) {
            parent::paintException($message);
            print "\n<br/><span class=\"fail\">Exception</span>:<br/>\n";
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br/>\n";
        }
        
        /**
         *    Paints formatted text such as dumped variables.
         *    @param string $message        Text to show.
         *    @access public
         */
        function paintFormattedMessage($message) {
            print '<pre>' . $this->_htmlEntities($message) . '</pre>';
        }
        
        /**
         *    Character set adjusted entity conversion.
         *    @param string $message    Plain text or Unicode message.
         *    @return string            Browser readable message.
         *    @access protected
         */
        function _htmlEntities($message) {
            return htmlentities($message, ENT_COMPAT, $this->_character_set);
        }
    }