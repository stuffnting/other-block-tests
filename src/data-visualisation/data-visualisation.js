const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;

import metadata from "./data-visualisation.json";

registerBlockType(metadata, {
  edit: (props) => {
    const { attributes, setAttributes } = props;
    const { sheetUrl, column, numberOfRows, barHeight, barGap } = attributes;

    return (
      <>
        <TextControl
          label="Google Sheets URL"
          help="(Must be publicly viewable.)"
          value={sheetUrl}
          onChange={(value) => setAttributes({ sheetUrl: value })}
        />
        <TextControl
          label="Sheet column to use"
          help="(Must be publicly viewable.)"
          value={column}
          onChange={(value) => setAttributes({ column: value })}
        />
        <TextControl
          label="Number of rows to get"
          help="(Must be an integer.)"
          value={numberOfRows}
          onChange={(value) => setAttributes({ numberOfRows: parseInt(value) })}
        />
        <TextControl
          label="Height of bars on the graph"
          help="(Must be an integer.)"
          value={barHeight}
          onChange={(value) => setAttributes({ barHeight: parseInt(value) })}
        />
        <TextControl
          label="Gap between bars on the graph"
          help="(Must be an integer.)"
          value={barGap}
          onChange={(value) => setAttributes({ barGap: parseInt(value) })}
        />
      </>
    );
  },
  save: () => {
    // For dynamic blocks return null
    return null;
  },
});
