<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script>
    (function () {
        function parseDisabledDates(input) {
            const disabled = input.dataset.disabledDates;
            if (!disabled) {
                return null;
            }

            try {
                const parsed = JSON.parse(disabled);
                return Array.isArray(parsed) ? parsed : null;
            } catch (error) {
                console.warn('Invalid disabled dates data for input:', input, error);
                return null;
            }
        }

        function resolveFormat(input) {
            return input.dataset.dateFormat || 'YYYY-MM-DD';
        }

        function buildOptions(input) {
            const options = {
                element: input,
                format: resolveFormat(input),
                autoRefresh: true,
                allowRepick: true,
                dropdowns: {
                    months: true,
                    years: true
                },
                buttonText: {
                    previousMonth: '&lt;',
                    nextMonth: '&gt;'
                }
            };

            if (input.value) {
                options.startDate = input.value;
            }

            if (input.min) {
                options.minDate = input.min;
            }

            if (input.max) {
                options.maxDate = input.max;
            }

            const lockDays = parseDisabledDates(input);
            if (lockDays && lockDays.length > 0) {
                options.lockDays = lockDays;
            }

            return options;
        }

        function initLitepickerInput(input) {
            if (input.dataset.litepickerInitialized === 'true' || input.readOnly || input.disabled) {
                return;
            }

            const options = buildOptions(input);
            const picker = new Litepicker(options);

            input.dataset.litepickerInitialized = 'true';

            input.addEventListener('change', function () {
                if (!input.value) {
                    picker.clearSelection();
                }
            });
        }

        function initLitepicker(container = document) {
            if (typeof Litepicker === 'undefined') {
                console.warn('Litepicker library is not available.');
                return;
            }

            const inputs = container.querySelectorAll('input[type="date"]:not([data-litepicker-manual="true"])');
            inputs.forEach(initLitepickerInput);
        }

        window.SigapLitepicker = {
            init: initLitepicker
        };

        document.addEventListener('DOMContentLoaded', function () {
            initLitepicker();
        });
    })();
</script>



