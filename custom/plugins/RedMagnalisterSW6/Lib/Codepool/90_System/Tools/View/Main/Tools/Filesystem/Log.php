<?php if (!class_exists('ML', false))
    throw new Exception(); ?>
<form action="<?php echo $this->getCurrentUrl() ?>" method="post">
    <div style="display:none">
        <?php foreach (MLHttp::gi()->getNeededFormFields() as $sKey => $sValue) { ?>
            <input type="hidden" name="<?php echo $sKey ?>" value="<?php echo $sValue ?>" />
        <?php } ?>
    </div>
    <div>
        <table class="attributesTable">
            <tr>
                <td>
                    <select value="" name="<?php echo MLHttp::gi()->parseFormFieldName('logfile'); ?>">
                        <option value="">..</option>
                        <?php foreach ($this->getFileList() as $sFile) { ?>
                            <option value="<?php echo $sFile; ?>" <?php if (MLRequest::gi()->data('logfile') == $sFile) { ?>
                                    selected="selected" <?php } ?>><?php echo $sFile; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ml-sku">Pattern 1 :</label>
                </td>
                <td>
                    <input type="text" name="<?php echo MLHttp::gi()->parseFormFieldName('pattern1'); ?>"
                        value="<?php echo MLRequest::gi()->data('pattern1') === null ? '' : MLRequest::gi()->data('pattern1'); ?>"
                        placeholder="regex-pattern (*required)" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ml-sku">Pattern 2 :</label>
                </td>
                <td>
                <input type="text" name="<?php echo MLHttp::gi()->parseFormFieldName('pattern2'); ?>"
                        value="<?php echo MLRequest::gi()->data('pattern2') === null ? '' : MLRequest::gi()->data('pattern2'); ?>"
                        placeholder="regex-pattern (*required)" />
                </td>
            </tr>

            <tr>
                <td>
                    <label for="ml-sku">Pattern 3 :</label>
                </td>
                <td>
                <input type="text" name="<?php echo MLHttp::gi()->parseFormFieldName('pattern3'); ?>"
                        value="<?php echo MLRequest::gi()->data('pattern3') === null ? '' : MLRequest::gi()->data('pattern3'); ?>"
                        placeholder="regex-pattern (*required)" />
                </td>
            </tr>
            <tr>
                <td>
                    <input class="mlbtn" type="submit" value='Show' />
                    <input class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('Zip'); ?>"
                        value='Zip' />
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</form>
<?php
if ($this->getOldContents()) {
    ?>
    <ul>Old Log-Files<?php
    foreach ($this->getOldContents() as $sOldLogFile) {
        try {
            $oldCacheURL = MLHttp::gi()->getCacheUrl('../log/old/' . $sOldLogFile);
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug($ex);
            $oldCacheURL = '';
        }
        ?>
            <li>
                <a class="ml-js-noBlockUi" href="<?php echo $oldCacheURL ?>" /><?php echo $sOldLogFile; ?></a>
            </li><?php
    }
    ?>
    </ul>
    <?php
}
?>
<?php if (MLRequest::gi()->data('Zip') !== null) {
    $sZipLogFile = $this->getContents();
    try {
        $cacheURL = MLHttp::gi()->getCacheUrl('../log/' . $sZipLogFile);
    } catch (\Exception $ex) {
        MLMessage::gi()->addDebug($ex);
        $cacheURL = '';
    }
    ?>
    <ul>Current Log-Zip-Files
        <li>
            <a class="ml-js-noBlockUi" href="<?php echo $cacheURL ?>" /><?php echo $sZipLogFile; ?></a>
        </li>
    </ul><?php
} else if ($this->getContents()) { ?>
        <table border="1" style="width:100%;border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Date
                    <th>Build</th>
                    </th>
                    <th style="width:80%;">Data</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->getContents() as $iLine => $aLine) { ?>
                    <tr>
                        <td><?php echo $iLine; ?></td>
                        <td><?php echo $aLine['date']; ?></td>
                        <td><?php echo $aLine['build']; ?></td>
                        <td>
                            <?php
                            $aJson = json_decode($aLine['data'], true);
                            if ($aJson === null) {
                                echo $aLine['data'];
                            } else {
                                ?>
                                <table border="1" style="width:100%;border-collapse;">
                                    <thead>
                                        <?php
                                        foreach ($aJson as $sKey => $mValue) {
                                            ?>
                                            <tr>
                                                <th><?php echo $sKey; ?></th>
                                                <td style="width:80%;">
                                                <?php if (is_array($mValue)) {
                                                    new dBug($mValue, '', true);
                                                } else {
                                                    echo $mValue;
                                                }
                                                ; ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                        </tr>
                        </thead>
                </table>
            <?php
                            }
                            ?>
            </td>
            </tr>
    <?php } ?>
        </tbody>
        </table>
<?php } ?>