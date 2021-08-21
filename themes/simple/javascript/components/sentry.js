import * as Sentry from "@sentry/browser";
import {Integrations} from "@sentry/tracing";

const ingest = '24ea1c6bcb214ec9a44071e7d99af546@o299871';

Sentry.init({
    dsn: `https://${ingest}.ingest.sentry.io/5918814`,
    integrations: [
        new Integrations.BrowserTracing(),
    ],

    tracesSampleRate: 1.0,
});
