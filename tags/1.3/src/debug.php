<?php

if (!empty($timeStart)) {
    $timeEnd = microtime(true);
    $timeDiff = $timeEnd-$timeStart;

    if (!empty($timeDiff)) {
        $timeDiff = round($timeDiff,3);
        $requiredFiles = get_required_files();

        echo '
        <div style="padding:1.5em; border:5px solid black; background:silver; margin:auto auto; margin-top:2em; margin-bottom:2em; width:50em; border-radius:15px;">
            <div style="font-size:1.4em; padding-bottom:0.5em; border-bottom:1px dotted gray; margin-bottom:1em;">Debugging</div>
            <strong>Total execution time:</strong> '.$timeDiff.' seconds<br />
            <strong>Total Memory Usage (MB): </strong>'.number_format(memory_get_usage() / (1024 * 1024), 4, '.', '').'<br />
            <strong>Peak Memory Usage (MB): </strong>'.number_format(memory_get_peak_usage() / (1024 * 1024), 4, '.', '').'<br />
            <strong>Total required files:</strong> '.count($requiredFiles);
        echo '<div style="padding:1em 1em 1em 3em;"><em>';

        foreach($requiredFiles as $requiredFile) {
            echo $requiredFile.'<br />';
        }

        echo '</em></div>';

        $dbt = new DB();
        $dbt = $dbt->getLog();

        echo '<strong>SQL query count:</strong> '.count($dbt);

        if (count($dbt) > 0) {
            echo '<div style="padding:1em 1em 1em 3em;"><em>';

            foreach ($dbt as $data) {
                echo $data.'<br />';
            }

            echo '</em></div>';
        }

        echo '</div>';
    }
}