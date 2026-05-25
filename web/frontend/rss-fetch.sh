#!/bin/bash
PORTFOLIO="/home/chris/dashboard/web/frontend/public/portfolio"
FEEDS="$PORTFOLIO/feeds.json"
OUTPUT="$PORTFOLIO/data/news.json"
SCRIPT="/home/chris/dashboard/web/frontend/rss-fetch.mjs"

mkdir -p "$PORTFOLIO/data"

node "$SCRIPT" "$FEEDS" "$OUTPUT" 2>&1

cp "$OUTPUT" "/home/chris/dashboard/web/frontend/public/data/news.json" 2>/dev/null
