(function($) {
    $(document).ready(function() {
        // Extend selected processing profile option with its optgroup name when selected and value != 0
        var $processingProfileSelects = $("select[name='ml[field][processingprofile]']");
        if ($processingProfileSelects.length) {
            // store original texts once
            $processingProfileSelects.find('option').each(function() {
                var $opt = $(this);
                if (typeof $opt.data('origText') === 'undefined') {
                    $opt.data('origText', $opt.text());
                }
            });

            var updateProcessingProfileLabel = function($select) {
                // reset all options to original text
                $select.find('option').each(function() {
                    var $opt = $(this);
                    var orig = $opt.data('origText');
                    if (typeof orig !== 'undefined') {
                        $opt.text(orig);
                    }
                });

                var $selected = $select.find('option:selected');
                var val = $selected.val();
                if (val !== undefined && val !== null && val !== '0' && $selected.length) {
                    var groupLabel = $selected.parent('optgroup').attr('label');
                    if (groupLabel && groupLabel.length) {
                        var baseText = $selected.data('origText') || $selected.text();
                        $selected.text(groupLabel + ': ' + baseText);
                    }
                }
            };

            $processingProfileSelects.on('change', function() {
                updateProcessingProfileLabel($(this));
            });

            // initialize on load
            $processingProfileSelects.each(function() {
                updateProcessingProfileLabel($(this));
            });
        }
    });
})(jqml);
