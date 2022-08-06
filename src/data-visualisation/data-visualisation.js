const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;

import metadata from "./data-visualisation.json";

registerBlockType(metadata, {
  edit: (props) => {
    const { attributes, setAttributes } = props;
    const { sheetUrl, column } = attributes;

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
      </>
    );
  },
  save: () => {
    // For dynamic blocks return null
    return null;
  },
});
