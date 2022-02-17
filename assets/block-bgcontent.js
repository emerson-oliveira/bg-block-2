(function (blocks, editor, element, components) {
  const el = element.createElement;

  const {
    InnerBlocks,
    InspectorControls,
    withColors,
    PanelColorSettings,
    getColorClassName,
  } = editor;
  const { registerBlockType } = blocks;
  const { Fragment } = element;

  const iconBlock = el(
    "svg",
    { width: 24, height: 24 },
    el("path", {
      d: "M4 4h7V2H4c-1.1 0-2 .9-2 2v7h2V4zm6 9l-4 5h12l-3-4-2.03 2.71L10 13zm7-4.5c0-.83-.67-1.5-1.5-1.5S14 7.67 14 8.5s.67 1.5 1.5 1.5S17 9.33 17 8.5zM20 2h-7v2h7v7h2V4c0-1.1-.9-2-2-2zm0 18h-7v2h7c1.1 0 2-.9 2-2v-7h-2v7zM4 13H2v7c0 1.1.9 2 2 2h7v-2H4v-7z",
    })
  );

  const colorSamples = [
    {
      name: "primary",
      slug: "bg-primary",
      color: "#000033",
    },
    {
      name: "secondary",
      slug: "bg-secondary",
      color: "#FFCA00",
    },
    {
      name: "gray lighter",
      slug: "bg-gray-lighter",
      color: "#f5f5f7",
    },
    {
      name: "gray light",
      slug: "bg-gray-light",
      color: "#cccccc",
    },
    {
      name: "gray",
      slug: "bg-gray",
      color: "#333333",
    },
    {
      name: "gray dark",
      slug: "bg-gray-dark",
      color: "#202020",
    },
    {
      name: "white",
      slug: "bg-white",
      color: "#ffffff",
    },
    {
      name: "black",
      slug: "bg-black",
      color: "#000000",
    },
  ];

  registerBlockType("mcontigo/bgcontentcolor", {
    title: "BG Color CMC",
    icon: iconBlock,
    category: "widgets",
    keywords: ["div", "group", "bg", "background"],
    attributes: {
      bgContentColor: {
        type: "string",
      },
      customBgContentColor: {
        type: "string",
      },
      block_style: {
        selector: "div",
        source: "attribute",
        attribute: "style",
      },
    },

    edit: withColors(
      "bgContentColor",
      "formTxtColor"
    )(function (props) {
      const formClasses = (
        (props.bgContentColor.class || "") +
        " " +
        props.className
      ).trim();

      const formStyles = {
        backgroundColor: props.bgContentColor.class
          ? undefined
          : props.attributes.customBgContentColor,
        padding: "10px",
      };

      return el(
        Fragment,
        {},

        el(
          InspectorControls,
          {},
          el(PanelColorSettings, {
            title: "BG Block Color",
            colorSettings: [
              {
                colors: colorSamples,
                value: props.bgContentColor.color,
                label: "Selected Color",
                onChange: props.setBgContentColor,
              },
            ],
          })
        ),

        el(
          "div",
          { className: `${formClasses} full-width`, style: formStyles },
          el(InnerBlocks)          
        )
      );
    }),

    save: function (props) {
      const formClass = getColorClassName(
        "form-color",
        props.attributes.bgContentColor
      );

      const formClasses = formClass || "";

      const formStyles = {
        backgroundColor: formClass
          ? undefined
          : props.attributes.customBgContentColor,
      };

      return el(
        "div",
        { className: `${formClasses} full-width`, style: formStyles },
        el("div", { className: "container-bg-color"}, el(InnerBlocks.Content))
      );
    },
  });

  registerBlockType("mcontigo/bgcontent", {
    title: "BG Content CMC",
    icon: iconBlock,
    category: "widgets",
    keywords: ["div", "group", "bg", "background"],
    attributes: {
      bgContentColor: {
        type: "string",
      },
      customBgContentColor: {
        type: "string",
      },
    },

    // The "edit" property must be a valid function.
    edit: withColors(
      "bgContentColor",
      "formTxtColor"
    )(function (props) {
      const formClasses = (
        (props.bgContentColor.class || "") +
        " " +
        props.className
      ).trim();

      const formStyles = {
        backgroundColor: props.bgContentColor.class
          ? undefined
          : props.attributes.customBgContentColor,
      };

      return el(
        Fragment,
        {},

        // Color Settings
        /*
        el(
          InspectorControls,
          {},
          el(PanelColorSettings, {
            title: "BG Block Color",
            colorSettings: [
              {
                colors: colorSamples,
                value: props.bgContentColor.color,
                label: "Selected Color",
                onChange: props.setBgContentColor,
              },
            ],
          })
        ),
        */

        // Block markup
        el(
          "div",
          { className: `${formClasses} box-with-bg`, style: formStyles },
          el(InnerBlocks)
        )
      );
    }),

    save: function (props) {
      const formClass = getColorClassName(
        "form-color",
        props.attributes.bgContentColor
      );

      const formClasses = formClass || "";

      const formStyles = {
        backgroundColor: formClass
          ? undefined
          : props.attributes.customBgContentColor,
      };

      return el(
        "div",
        { className: `${formClasses} box-with-bg`, style: formStyles },
        el(InnerBlocks.Content)
      );
    },
  });
})(window.wp.blocks, window.wp.editor, window.wp.element, window.wp.components);
