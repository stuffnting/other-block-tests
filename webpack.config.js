const defaultConfig = require("@wordpress/scripts/config/webpack.config");

const fs = require("fs");
const path = require("path");

const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const CopyPlugin = require("copy-webpack-plugin");

const { BlockList } = require("net");

// Get the build list from the JSON file. `require` converts JSON to an object.
const BUILD_LIST_DATA = require("./src/build-list.json");

/**
 * Make an array of the block names that need building.
 *
 * Make an array of blocks from BUILD_LIST_DATA
 * Filter out the "include" : false.
 * Map each `name` to a new array.
 */
const build_list_arr = Object.values(BUILD_LIST_DATA)
  .filter((el) => (el.include ? true : false))
  .map((el) => el.name);

/**
 * Make a build list object for Webpack.
 *
 * Each property is name: source
 * ./src/${el}/${el}.js is in form ./src/block-name/block-name.js
 *
 * Some block examples don't have a JS file.
 * fs.existsSync(`./src/${el}/${el}.js`) checks the existence of the file before
 * placing in buildListObj.
 */
const buildListObj = build_list_arr.length
  ? build_list_arr.reduce(
      (acc, el) =>
        fs.existsSync(`./src/${el}/${el}.js`)
          ? {
              ...acc,
              [el]: `./src/${el}/${el}.js`,
            }
          : {
              ...acc,
            },
      {}
    )
  : {};

/**
 * A callback function for the copy-webpack-plugin `filter`.
 *
 * When a file matches the `from` glob, decide wether to copy it  * or not
 * based on the contents of buildListObj.
 *
 * @param {string} absoluteSourcePath - Absolute path to the file that matched the glob.
 * @returns {boolean} - Whether to copy the file to the build folder.
 */
const filterCB = (absoluteSourcePath) => {
  var pathArray = absoluteSourcePath.split("/");
  var fileDirectory = pathArray.slice(-2, -1).join();

  if (build_list_arr.includes(fileDirectory)) {
    return true;
  }
  return false;
};

/**
 * Define the more complex copy-webpack-plugin patterns here.
 */
const readmePattern = {
  context: "src",
  from: "*/README.md",
  to: "./[path]/README.md",
  filter: filterCB,
  noErrorOnMissing: true,
};

const phpPattern = {
  context: "src",
  from: "*/*.php",
  to: "./[path]/index.php",
  filter: filterCB,
  noErrorOnMissing: true,
};

const cssPattern = {
  context: "src",
  from: "*/*.css",
  to: "./[path]/styles.css",
  filter: filterCB,
  noErrorOnMissing: true,
};

const jsonPattern = {
  context: "src",
  from: "*/*.json",
  to({ absoluteFilename }) {
    var pathArray = absoluteFilename.split("/");
    var fileName = pathArray.slice(-1).join();
    var name = fileName.split(".").slice(0, 1).join();
    var fileDirectory = pathArray.slice(-2, -1).join();

    /**
     * ./src/block-name/block-name.json becomes ./start/block-name/block.json
     * ./src/block-name/any-other-name.json becomes ./start/block-name/any-other-name.json
     */
    return name === fileDirectory
      ? "./[path]/block.json"
      : "./[path]/[name].json";
  },
  filter: filterCB,
  noErrorOnMissing: true,
};

module.exports = {
  ...defaultConfig,
  externals: {
    lodash: "lodash",
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        // Single files
        { from: "README.md", to: "./" }, // Main README.md
        { context: "src", from: "plugin.php", to: "./" },
        { context: "src", from: "build-list.json", to: "./" },
        // Patterns
        readmePattern,
        phpPattern,
        cssPattern,
        jsonPattern,
      ],
    }),
    new CleanWebpackPlugin({
      dry: false,
      cleanOnceBeforeBuildPatterns: ["**/*"],
    }),
  ],
  entry: buildListObj,
  output: {
    path: path.join(__dirname, "/start"),
    filename: "[name]/index.js",
  },
};
