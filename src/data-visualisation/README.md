# A block to try out data visualisation in WordPress

This is taken from [a video tutorial](https://www.youtube.com/watch?v=DzN5Wqtq5h8), for which [slides are available](https://talks.jhalabi.com/datavis/#/10). (By the way, the slides are made with [revealjs](https://revealjs.com/).)

The plugin links to a Google spreadsheet using an API key which is stored in an AFC options page.

The block stores the Google spreadsheet URL as an attribute, and uses dynamic PHP serverside rendering to create a bar-chart.

The block's attributes are:

- the spreadsheet column from which to draw the data;
- the number of rows from which to get data;
- the height of the chart's bars;
- and the the gap between the bars
