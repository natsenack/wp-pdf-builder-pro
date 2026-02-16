module.exports = {
  plugins: {
    autoprefixer: {
      overrideBrowserslist: [
        'last 2 versions',
        '> 1%',
        'IE 11',
        'not dead'
      ],
      grid: 'autoplace'
    }
  }
};
