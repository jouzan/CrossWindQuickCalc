# Crosswind calculator

Ready-to-host website with large text, blue/cyan colors, airport runway comparisons, and local time-zone entry converted to UTC.

## Upload to your host

Upload these files and the `api` folder to your PHP web hosting directory:

- `index.html` — website page
- `styles.css` — styling
- `app.js` — complete interactive calculator
- `app.js.LEGAL.txt` — bundled software license notices
- `config.js` — weather API URL
- `api/briefing.php` — live weather backend

Requires **PHP 8.1 or newer with cURL enabled** and outbound HTTPS access. No package installation, build step, Node server, API key, or ChatGPT sign-in is needed. Open the hosted website through its HTTPS URL. Opening the HTML directly from disk will not run the weather backend.

## Time zones

Select the input time zone, then enter the flight date and time range. MST (Arizona) is the default, fixed UTC−07:00. PST is fixed UTC−08:00. Choose PDT, MDT, CDT, EDT, or AKDT separately when daylight time applies. The converted UTC window is shown and used for forecast calculations. Changing the time zone preserves the flight window and converts the displayed times.

## GitHub

These files can be committed directly to a GitHub repository. **GitHub Pages cannot run PHP**: use PHP hosting for the full live-weather site. Pages requires a separately hosted weather backend configured in `config.js`, with CORS configured for your Pages origin.

## Data and limits

METAR, TAF, and runway metadata: NOAA Aviation Weather Center. MOS guidance: Iowa Environmental Mesonet. Requests are cached for five minutes. The compass is schematic. Wind components use true headings; magnetic headings use published airport variation. Each direction shows the highest gust crosswind forecast case in your window. Conditional groups are included; groups without decoded winds are omitted. MOS points are not interpolated. Variable winds show separate conservative bounds.

NOTAM runway closures are not automatically verified. Use the official NOTAM link and confirm current weather, runway availability, and aircraft limitations before flight. The calculator is a planning aid.
