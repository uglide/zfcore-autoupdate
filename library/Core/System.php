<?php
/**
 * Helper class for work with system
 *
 * @category
 * @package
 * @subpackage
 *
 * @author Igor Malinovskiy <glide.name>
 * @file Console.php
 * @date: 15.01.13
 * @time: 18:12
 */
class Core_System
{
    /**
     * @param      $cmd
     * @param bool $timeout
     *
     * @return string
     */
    public static function shellExec($cmd, $timeout = false)
    {
        $stdIn = '';
        $stdOut = '';
        $stdErr = '';

        $exitCode = self::executeWithTimeOut(
            $cmd, $stdIn, $stdOut, $stdErr, $timeout
        );

        if ($exitCode == 1) {
            return false;
        } else {
            return $stdOut;
        }
    }

    /**
     * Based on ross.vc code
     * @param      $cmd
     * @param null $stdin
     * @param      $stdout
     * @param      $stderr
     * @param bool $timeout
     *
     * @return int
     */
    public static function executeWithTimeOut($cmd, $stdin=null, &$stdout, &$stderr, $timeout=false)
    {
        $pipes = array();
        $process = proc_open(
            $cmd,
            array(array('pipe','r'),array('pipe','w'),array('pipe','w')),
            $pipes
        );
        $start = time();
        $stdout = '';
        $stderr = '';

        if(is_resource($process))
        {
            stream_set_blocking($pipes[0], 0);
            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);
            fwrite($pipes[0], $stdin);
            fclose($pipes[0]);
        }

        while(is_resource($process))
        {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            if($timeout !== false && time() - $start > $timeout)
            {
                proc_terminate($process, 9);
                $stderr = 'Process killed (exceeded time limit): ' . $cmd;
                return 1;
            }

            $status = proc_get_status($process);
            if(!$status['running'])
            {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return $status['exitcode'];
            }

            sleep(1);
        }

        return 1;
    }

}
