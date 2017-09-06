<div class="content">
    <div class="title">Something went wrong.</div>
@unless(empty($sentryID))
    <!-- Sentry JS SDK 2.1.+ required -->
        <script src="https://cdn.ravenjs.com/3.3.0/raven.min.js"></script>

        <script>
            Raven.showReportDialog({
                eventId: '{{ $sentryID }}',

                // use the public DSN (dont include your secret!)
                dsn: 'https://6f5510425e0343338c7c84eac1e36607@sentry.io/160493'
            });
        </script>
    @endunless
</div>