const { registerBlockType } = wp.blocks;
const { InnerBlocks, useBlockProps, InspectorControls, BlockControls } =
  wp.blockEditor;
const { DateTimePicker, PanelBody, TextControl } = wp.components;
const { useSelect } = wp.data;
const { useEntityProp } = wp.coreData;

import metadata from "./data-visualisation.json";

const ALLOWED_BLOCKS = ["core/paragraph", "core/heading", "core/list"];
const META_FIELD_OBJECT_NAME = "_myprefix_dynamic_meta_block_object";
const STYLE = {
  color: "white",
  padding: "20px",
  background: "midnightblue",
  border: "5px solid yellow",
};

registerBlockType(metadata, {
  edit: () => {
    const blockProps = useBlockProps({ style: STYLE });

    const postType = useSelect(
      (select) => select("core/editor").getCurrentPostType(),
      []
    );

    const [meta, setMeta] = useEntityProp("postType", postType, "meta");

    const metaFieldValue1 = meta[META_FIELD_OBJECT_NAME].field1 || "";
    const metaFieldValue2 = meta[META_FIELD_OBJECT_NAME].field2 || "";

    // Ths key is which item in the meta field array to use
    function updateMetaValue(newValue, fieldName) {
      const newMetaObj = Object.assign({}, meta[META_FIELD_OBJECT_NAME], {
        [fieldName]: newValue,
      });

      setMeta({ ...meta, [META_FIELD_OBJECT_NAME]: newMetaObj });
    }

    return (
      <>
        <div {...blockProps}>
          <InspectorControls>
            <PanelBody title="Meta Values" initialOpen={false}>
              <TextControl
                label="Text 1"
                help="Enter some text"
                value={metaFieldValue1}
                onChange={(newValue) => updateMetaValue(newValue, "field1")}
              />
              <TextControl
                label="Text 2"
                help="Enter some text"
                value={metaFieldValue2}
                onChange={(newValue) => updateMetaValue(newValue, "field2")}
              />
            </PanelBody>
          </InspectorControls>
          <p>{metaFieldValue1}</p>
          <p>{metaFieldValue2}</p>
          <InnerBlocks allowedBlocks={ALLOWED_BLOCKS} />
        </div>
      </>
    );
  },
  save: (props) => {
    const blockProps = useBlockProps.save({ style: STYLE });

    return (
      <div {...blockProps}>
        <InnerBlocks.Content />
      </div>
    );
  },
});
