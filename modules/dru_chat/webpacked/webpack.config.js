const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin")
const { CleanWebpackPlugin } = require("clean-webpack-plugin")
const path = require("path")

let mode = "development",
  target = "web",
  //source_map = "source-map"
  source_map = "source-map"

if (process.env.NODE_ENV === "production") {
  mode = "production"
  target = "browserslist"
  //target = "web"
  source_map = false
}

module.exports = {
  mode: mode,
  target: target,

  entry: {
    chat: './src/chat.js',
  },

  output: {
    assetModuleFilename: "images/[hash][ext][query]",
    filename: '[name].js',
    path: path.resolve(__dirname, '../libs')
  },

  plugins: [
    new MiniCssExtractPlugin({ filename: '[name].css'}),
    new CleanWebpackPlugin()
  ],

  module: {
    rules: [
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        type: "asset"
     },
      {
        test: /\.m?js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.(woff(2)?|ttf|eot|png|jpe?g|gif|svg)$/i,
        //type: "asset/inline", // in javascript file
        //type: "asset/resource" //output folder
        type: "asset" //  for auto
      },
      {
        test: /\.(s[ac]|c)ss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "postcss-loader",
          "sass-loader"
        ]
      }

    ],
    /*rules: [
       {
         test: /\.(s[ac]|c)ss$/i,
         use: [
           //'style-loader',
           MiniCssExtractPlugin.loader,
           'css-loader',
           'postcss-loader',
           // Compiles Sass to CSS
           'sass-loader'
         ]
       },

       {
         test: /\.m?js$/,
         exclude: /(node_modules|bower_components)/,
         use: {
           loader: 'babel-loader',
           options: {
             presets: ['@babel/preset-env']
           }
         }
       }
     ],*/
  },
  optimization: {
    minimizer: [
      // For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
      // `...`,
      new CssMinimizerPlugin(),
    ],
    //minimize: true,// to minimize even in dev
  },
  devtool: source_map,
}
