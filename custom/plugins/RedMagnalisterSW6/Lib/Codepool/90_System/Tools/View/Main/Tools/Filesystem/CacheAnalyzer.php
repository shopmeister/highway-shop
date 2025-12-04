<?php if (!class_exists('ML', false)) throw new Exception(); ?>
<?php
$aCacheData = $this->getCacheData();
$aData = $aCacheData['data'];
$aPagination = $aCacheData['pagination'];
$aFilters = $aCacheData['filters'];
$aSorting = $aCacheData['sorting'];
?>

<style>
.ml-cache-analyzer {
    width: 100%;
}
.ml-cache-analyzer table {
    border-collapse: collapse;
    width: 100%;
    margin: 10px 0;
}
.ml-cache-analyzer th,
.ml-cache-analyzer td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.ml-cache-analyzer th {
    background-color: #f2f2f2;
    font-weight: bold;
}
.ml-cache-analyzer tr:nth-child(even) {
    background-color: #f9f9f9;
}
.ml-cache-analyzer tr:hover {
    background-color: #f1f1f1;
}
.ml-cache-analyzer .expired {
    color: #d9534f;
    font-weight: bold;
}
.ml-cache-analyzer .valid {
    color: #5cb85c;
    font-weight: bold;
}
.ml-cache-analyzer .filter-section {
    background: #f5f5f5;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
}
.ml-cache-analyzer .filter-row {
    display: flex;
    gap: 10px;
    margin: 5px 0;
    flex-wrap: wrap;
}
.ml-cache-analyzer .filter-item {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}
.ml-cache-analyzer .filter-item label {
    font-weight: bold;
    margin-bottom: 3px;
}
.ml-cache-analyzer .filter-item input,
.ml-cache-analyzer .filter-item select {
    padding: 5px;
}
.ml-cache-analyzer .pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
    padding: 10px;
    background: #f5f5f5;
}
.ml-cache-analyzer .pagination-info {
    font-weight: bold;
}
.ml-cache-analyzer .pagination-controls {
    display: flex;
    gap: 5px;
}
.ml-cache-analyzer .expandable-content {
    cursor: pointer;
    color: #0066cc;
}
.ml-cache-analyzer .expandable-content:hover {
    text-decoration: underline;
}
.ml-cache-analyzer .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.ml-cache-analyzer .modal-content {
    background: white;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
}
.ml-cache-analyzer .modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f8f8;
    border-radius: 8px 8px 0 0;
}
.ml-cache-analyzer .modal-header h3 {
    margin: 0;
    font-size: 16px;
    word-break: break-all;
}
.ml-cache-analyzer .modal-close {
    background: #d9534f;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    font-size: 18px;
}
.ml-cache-analyzer .modal-close:hover {
    background: #c9302c;
}
.ml-cache-analyzer .modal-body {
    padding: 20px;
    overflow: auto;
    flex: 1;
}
.ml-cache-analyzer .modal-body pre {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 4px;
    overflow: auto;
    max-height: calc(90vh - 150px);
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    line-height: 1.4;
    margin: 0;
}
.ml-cache-analyzer .modal-actions {
    padding: 10px 20px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
    background: #f8f8f8;
    border-radius: 0 0 8px 8px;
}
.ml-cache-analyzer .actions-bar {
    margin: 10px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.ml-cache-analyzer .sortable {
    cursor: pointer;
    user-select: none;
}
.ml-cache-analyzer .sortable:hover {
    background-color: #e0e0e0;
}
.ml-cache-analyzer .sort-indicator {
    font-size: 10px;
    margin-left: 5px;
}
</style>

<div class="ml-cache-analyzer">
    <h2>Cache File Analyzer</h2>

    <!-- Search and Filter Section -->
    <form action="<?php echo $this->getCurrentUrl() ?>" method="post">
        <?php foreach (MLHttp::gi()->getNeededFormFields() as $sName => $sValue) { ?>
            <input type="hidden" name="<?php echo $sName ?>" value="<?php echo $sValue ?>"/>
        <?php } ?>

        <div class="filter-section">
            <h3>Search & Filters</h3>

            <div class="filter-row">
                <div class="filter-item">
                    <label for="search">Search Cache Key:</label>
                    <input type="text"
                           id="search"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('search'); ?>"
                           value="<?php echo htmlspecialchars(isset($aFilters['search']) ? $aFilters['search'] : ''); ?>"
                           placeholder="Enter cache key pattern..."/>
                </div>

                <div class="filter-item">
                    <label for="filter_expired">Expired Status:</label>
                    <select id="filter_expired" name="<?php echo MLHttp::gi()->parseFormFieldName('filter_expired'); ?>">
                        <option value="">All</option>
                        <option value="yes" <?php echo ($aFilters['expired'] === 'yes') ? 'selected' : ''; ?>>Expired Only</option>
                        <option value="no" <?php echo ($aFilters['expired'] === 'no') ? 'selected' : ''; ?>>Valid Only</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="perpage">Items Per Page:</label>
                    <select id="perpage" name="<?php echo MLHttp::gi()->parseFormFieldName('perpage'); ?>">
                        <option value="25" <?php echo ($aPagination['per_page'] == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo ($aPagination['per_page'] == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($aPagination['per_page'] == 100) ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo ($aPagination['per_page'] == 200) ? 'selected' : ''; ?>>200</option>
                    </select>
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-item">
                    <label for="filter_date_from">Created From:</label>
                    <input type="date"
                           id="filter_date_from"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('filter_date_from'); ?>"
                           value="<?php echo htmlspecialchars(isset($aFilters['date_from']) ? $aFilters['date_from'] : ''); ?>"/>
                </div>

                <div class="filter-item">
                    <label for="filter_date_to">Created To:</label>
                    <input type="date"
                           id="filter_date_to"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('filter_date_to'); ?>"
                           value="<?php echo htmlspecialchars(isset($aFilters['date_to']) ? $aFilters['date_to'] : ''); ?>"/>
                </div>

                <div class="filter-item">
                    <label for="filter_size_min">Min Size (bytes):</label>
                    <input type="number"
                           id="filter_size_min"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('filter_size_min'); ?>"
                           value="<?php echo $aFilters['size_min'] > 0 ? $aFilters['size_min'] : ''; ?>"
                           placeholder="0"/>
                </div>

                <div class="filter-item">
                    <label for="filter_size_max">Max Size (bytes):</label>
                    <input type="number"
                           id="filter_size_max"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('filter_size_max'); ?>"
                           value="<?php echo $aFilters['size_max'] > 0 ? $aFilters['size_max'] : ''; ?>"
                           placeholder="unlimited"/>
                </div>

                <div class="filter-item" style="flex-direction: row; align-items: center; gap: 5px;">
                    <input type="checkbox"
                           id="filter_key_pattern"
                           name="<?php echo MLHttp::gi()->parseFormFieldName('filter_key_pattern'); ?>"
                           value="exclude_ml_filesystem"
                           <?php echo ($aFilters['key_pattern'] === 'exclude_ml_filesystem') ? 'checked' : ''; ?>/>
                    <label for="filter_key_pattern" style="margin-bottom: 0;">Hide ML_FILESYSTEM</label>
                </div>
            </div>

            <div class="filter-row">
                <input type="hidden" name="<?php echo MLHttp::gi()->parseFormFieldName('sortby'); ?>" id="sortby" value="<?php echo $aSorting['sortby']; ?>"/>
                <input type="hidden" name="<?php echo MLHttp::gi()->parseFormFieldName('sortorder'); ?>" id="sortorder" value="<?php echo $aSorting['sortorder']; ?>"/>
                <input type="hidden" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" id="page" value="1"/>
                <input class="mlbtn" type="submit" value="Apply Filters & Search"/>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <input class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('delete_selected'); ?>" value="Delete Selected" onclick="return confirm('Are you sure you want to delete the selected cache entries?');"/>
            <input class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('delete_expired'); ?>" value="Delete All Expired" onclick="return confirm('Are you sure you want to delete all expired cache entries?');"/>
            <input class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('export'); ?>" value="csv" title="Export as CSV"/>
            <input class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('export'); ?>" value="json" title="Export as JSON"/>
        </div>

        <!-- Pagination Info -->
        <div class="pagination">
            <div class="pagination-info">
                Showing <?php echo $aPagination['offset'] + 1; ?> - <?php echo min($aPagination['offset'] + $aPagination['per_page'], $aPagination['filtered_count']); ?>
                of <?php echo $aPagination['filtered_count']; ?> filtered items
                <?php if ($aPagination['filtered_count'] != $aPagination['total_count']) { ?>
                    (<?php echo $aPagination['total_count']; ?> total)
                <?php } ?>
            </div>
            <div class="pagination-controls">
                <?php if ($aPagination['page'] > 1) { ?>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="1">First</button>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['page'] - 1; ?>">Previous</button>
                <?php } ?>

                <span>Page <?php echo $aPagination['page']; ?> of <?php echo max(1, $aPagination['total_pages']); ?></span>

                <?php if ($aPagination['page'] < $aPagination['total_pages']) { ?>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['page'] + 1; ?>">Next</button>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['total_pages']; ?>">Last</button>
                <?php } ?>
            </div>
        </div>

        <!-- Cache Data Table -->
        <?php if (!empty($aData)) { ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">
                        <input type="checkbox" id="select-all"/>
                    </th>
                    <th class="sortable" data-sort="key">
                        Cache Key
                        <?php if ($aSorting['sortby'] === 'key') { ?>
                            <span class="sort-indicator"><?php echo $aSorting['sortorder'] === 'asc' ? '▲' : '▼'; ?></span>
                        <?php } ?>
                    </th>
                    <th class="sortable" data-sort="expired">
                        Status
                        <?php if ($aSorting['sortby'] === 'expired') { ?>
                            <span class="sort-indicator"><?php echo $aSorting['sortorder'] === 'asc' ? '▲' : '▼'; ?></span>
                        <?php } ?>
                    </th>
                    <th class="sortable" data-sort="size">
                        Size
                        <?php if ($aSorting['sortby'] === 'size') { ?>
                            <span class="sort-indicator"><?php echo $aSorting['sortorder'] === 'asc' ? '▲' : '▼'; ?></span>
                        <?php } ?>
                    </th>
                    <th class="sortable" data-sort="created">
                        Created
                        <?php if ($aSorting['sortby'] === 'created') { ?>
                            <span class="sort-indicator"><?php echo $aSorting['sortorder'] === 'asc' ? '▲' : '▼'; ?></span>
                        <?php } ?>
                    </th>
                    <th class="sortable" data-sort="expires">
                        Expires
                        <?php if ($aSorting['sortby'] === 'expires') { ?>
                            <span class="sort-indicator"><?php echo $aSorting['sortorder'] === 'asc' ? '▲' : '▼'; ?></span>
                        <?php } ?>
                    </th>
                    <th>Content</th>
                </tr>
            </thead>
            <tbody>
                <?php $iIndex = 0; foreach ($aData as $aItem) { $iIndex++; ?>
                <tr>
                    <td>
                        <input type="checkbox"
                               class="cache-selector"
                               name="<?php echo MLHttp::gi()->parseFormFieldName('selected[]'); ?>"
                               value="<?php echo htmlspecialchars($aItem['sKey']); ?>"/>
                    </td>
                    <td title="<?php echo htmlspecialchars($aItem['sFilePath']); ?>">
                        <?php echo htmlspecialchars($aItem['sKey']); ?>
                    </td>
                    <td>
                        <span class="<?php echo $aItem['blExpired'] ? 'expired' : 'valid'; ?>">
                            <?php echo $aItem['blExpired'] ? 'Expired' : 'Valid'; ?>
                        </span>
                    </td>
                    <td><?php echo number_format($aItem['iFileSize']); ?> bytes</td>
                    <td><?php echo $aItem['mCreatedTime'][1]; ?></td>
                    <td><?php echo htmlspecialchars(isset($aItem['sExpirationDateFormatted']) ? $aItem['sExpirationDateFormatted'] : 'Unknown'); ?></td>
                    <td>
                        <span class="expandable-content"
                              data-cachekey="<?php echo htmlspecialchars($aItem['sKey']); ?>"
                              data-index="<?php echo $iIndex; ?>">
                            [Click to view]
                        </span>
                        <script type="text/plain" id="cache-content-<?php echo $iIndex; ?>" style="display:none;"><?php echo htmlspecialchars($aItem['mContent']); ?></script>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <p><strong>No cache files found matching your criteria.</strong></p>
        <?php } ?>

        <!-- Bottom Pagination -->
        <?php if (!empty($aData)) { ?>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?php echo $aPagination['offset'] + 1; ?> - <?php echo min($aPagination['offset'] + $aPagination['per_page'], $aPagination['filtered_count']); ?>
                of <?php echo $aPagination['filtered_count']; ?> filtered items
            </div>
            <div class="pagination-controls">
                <?php if ($aPagination['page'] > 1) { ?>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="1">First</button>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['page'] - 1; ?>">Previous</button>
                <?php } ?>

                <span>Page <?php echo $aPagination['page']; ?> of <?php echo max(1, $aPagination['total_pages']); ?></span>

                <?php if ($aPagination['page'] < $aPagination['total_pages']) { ?>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['page'] + 1; ?>">Next</button>
                    <button class="mlbtn" type="submit" name="<?php echo MLHttp::gi()->parseFormFieldName('page'); ?>" value="<?php echo $aPagination['total_pages']; ?>">Last</button>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </form>

    <!-- Modal for displaying cache content -->
    <div class="modal-overlay" id="cacheModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Cache Content</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <pre id="modalContent"></pre>
            </div>
            <div class="modal-actions">
                <button class="mlbtn" id="copyContent">Copy to Clipboard</button>
                <button class="mlbtn" id="downloadContent">Download</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($) {
    var currentCacheKey = '';
    var currentContent = '';

    // Select all checkbox functionality
    $('#select-all').click(function() {
        $('.cache-selector').prop('checked', $(this).prop('checked'));
    });

    // Decode HTML entities
    function decodeHtmlEntities(text) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
    }

    // Pretty-print JSON
    function prettyPrintJSON(content) {
        try {
            var parsed = JSON.parse(content);
            return JSON.stringify(parsed, null, 2);
        } catch (e) {
            // Not JSON or invalid JSON, return as-is
            return content;
        }
    }

    // Show modal with cache content
    $('.expandable-content').click(function(e) {
        e.preventDefault();

        currentCacheKey = $(this).data('cachekey');
        var index = $(this).data('index');

        // Get content from hidden script tag and decode HTML entities
        var rawContent = $('#cache-content-' + index).text();
        currentContent = decodeHtmlEntities(rawContent);

        // Pretty print if it's JSON
        var displayContent = prettyPrintJSON(currentContent);

        $('#modalTitle').text('Cache: ' + currentCacheKey);
        $('#modalContent').text(displayContent);
        $('#cacheModal').css('display', 'flex');
    });

    // Close modal
    function closeModal() {
        $('#cacheModal').css('display', 'none');
    }

    $('#closeModal').click(closeModal);

    // Close modal when clicking outside
    $('#cacheModal').click(function(e) {
        if (e.target.id === 'cacheModal') {
            closeModal();
        }
    });

    // Close modal with ESC key
    $(document).keydown(function(e) {
        if (e.key === 'Escape' && $('#cacheModal').css('display') === 'flex') {
            closeModal();
        }
    });

    // Copy to clipboard
    $('#copyContent').click(function() {
        var content = $('#modalContent').text();

        // Create temporary textarea
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(content).select();
        document.execCommand('copy');
        $temp.remove();

        // Visual feedback
        var originalText = $(this).text();
        $(this).text('Copied!');
        setTimeout(function() {
            $('#copyContent').text(originalText);
        }, 2000);
    });

    // Download content
    $('#downloadContent').click(function() {
        var content = $('#modalContent').text();
        var filename = currentCacheKey + '.txt';

        // Create blob and download
        var blob = new Blob([content], { type: 'text/plain' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    });

    // Sortable column headers
    $('.sortable').click(function() {
        var sortBy = $(this).data('sort');
        var currentSortBy = $('#sortby').val();
        var currentSortOrder = $('#sortorder').val();

        // Toggle sort order if clicking the same column
        if (sortBy === currentSortBy) {
            $('#sortorder').val(currentSortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            $('#sortby').val(sortBy);
            $('#sortorder').val('desc');
        }

        // Reset to first page when sorting changes
        $('#page').val(1);

        // Submit the form
        $(this).closest('form').submit();
    });
})(jqml);
</script>