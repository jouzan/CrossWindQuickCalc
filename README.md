# Crosswind — one HTML file

Upload **index.html** to GitHub Pages. That is the entire website.

All styling and vanilla JavaScript are inside the HTML file. No PHP, React, packages, build step, backend, Worker, configuration, API keys, or ChatGPT login are needed.

## Use

Enter a four-character ICAO airport code, select your time zone, and choose the flight window. Click Calculate crosswind. Compare TAF and MOS guidance or use manual winds. Select a runway direction in the comparison table to see headwind or tailwind for that direction.

MST defaults to fixed UTC−07:00 (Arizona). PST is fixed UTC−08:00. Select daylight time separately where applicable. The exact UTC window is shown; changing zones preserves your flight window and converts the displayed clock times.

## How it gets data

The browser directly requests public feeds that permit cross-origin access:

- TAF: https://mesonet.agron.iastate.edu/api/1/nws/taf.json
- MOS: https://mesonet.agron.iastate.edu/api/1/mos.json
- Airport station metadata: https://mesonet.agron.iastate.edu/api/1/station/
- Latest observations: https://mesonet.agron.iastate.edu/json/current.py
- True runway headings: https://github.com/davidmegginson/ourairports-data

The runway CSV is downloaded once per page session. Internet access is needed for live data. No third-party CORS proxy is used. Provider availability and browser access policies may change. The UI shows unavailable products rather than substituting example weather. Forecast and observation availability varies by airport, particularly outside the US. Only stations with IEM ASOS metadata get the observation panel.

## Calculation limits

All headings are true. Each runway direction uses its highest gust-crosswind forecast case in your selected window; sustained components use the same case. Prevailing TAF groups extend to the next prevailing group or expiry. Conditional groups use the decoded end time, or conservatively extend to expiry when no end is provided. Groups without decoded wind speed are omitted. MOS points are not interpolated. Variable winds show separate full-speed crosswind and tailwind bounds. The compass is schematic.

OurAirports closed flags are not live NOTAM checks. Always verify runway availability, weather, ATIS, and aircraft limitations through appropriate briefing sources. Official NOTAM search is linked in the UI. The personal crosswind reference is not an aircraft operating limit.

## Verification

Direct browser loading was verified for KIWA and KPHX using only a static file server. UTC conversion, date rollovers, quoted CSV parsing, crosswind and tailwind formulas, and forecast period selection were checked. No custom backend was running.

## Aircraft POH comparison

Choose the exact aircraft profile in the input form. The added table column compares the runway's highest gust crosswind (or sustained wind when no gust is reported) with the selected published reference. It shows below, at, or above the reference, not a safe/unsafe operating decision. The personal crosswind reference in the result card remains separate.

Profiles checked against public manufacturer-authored manuals on 17 October 2026:

| Selectable profile | Reference | Public source |
| --- | --- | --- |
| DA20-C1 | 20 kt maximum demonstrated | [DA202-C1, Rev 29, §2.16 / §5.3.4](https://flygenesis.ca/wp-content/uploads/2025/09/DA20-C1_POH-compressed-Rev-29.pdf) |
| DA40 NG | 25 kt maximum demonstrated | [AFM 6.01.15-E, Rev 4, §5.3.6, p. 5-10](https://studylib.net/doc/28537020/d40-afm) |
| Cessna 172S NAV III / KAP 140 | 15 kt maximum demonstrated | [172SPHAUS public POH, §5, Figure 5-4, p. 5-13](https://s3.amazonaws.com/atp-program-docs/manuals/aircraft/ce-172sp.pdf) |
| Cessna 182T, source 2001 edition | 15 kt maximum demonstrated | [Model 182T POH, 30-Apr-2001, §4, p. 4-5](https://azflyers.org/resources/POH/Model182T_POH.pdf) |
| Legacy RV-12, Rev 19 | 11 kt maximum direct crosswind, listed in limitations | [RV-12 POH, 22-Feb-2024, §2, p. 2-2](https://www.vansaircraft.com/wp-content/uploads/2022/10/RV-12-POH-Rev-19.pdf) |

These are identified source editions, not a claim that each is the current applicable handbook for every aircraft. The RV-12iS is a different profile: its Rev 13 handbook describes an 11 kt maximum demonstrated component. Newer Cessna 172S editions can specify different values by flap configuration. DA40 variants other than NG can differ. Confirm your serial-specific approved POH/AFM, revision, supplements, and configuration before relying on a profile. A demonstrated value does not guarantee controllability or safe runway suitability. The comparison does not evaluate tailwind limits.
