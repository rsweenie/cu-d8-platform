report({
  "testSuite": "BackstopJS",
  "tests": [
    {
      "pair": {
        "reference": "../bitmaps_reference/prod_test___0_document_0_phone.png",
        "test": "../bitmaps_test/20190920-112812/prod_test___0_document_0_phone.png",
        "selector": "document",
        "fileName": "prod_test___0_document_0_phone.png",
        "label": "/",
        "misMatchThreshold": 0.1,
        "url": "http://local.creighton.com//",
        "referenceUrl": "https://alliance.creighton.edu/",
        "expect": 0,
        "viewportLabel": "phone",
        "diff": {
          "isSameDimensions": false,
          "dimensionDifference": {
            "width": 0,
            "height": 25
          },
          "misMatchPercentage": "44.07",
          "analysisTime": 146
        },
        "diffImage": "../bitmaps_test/20190920-112812/failed_diff_prod_test___0_document_0_phone.png"
      },
      "status": "fail"
    },
    {
      "pair": {
        "reference": "../bitmaps_reference/prod_test___0_document_1_tablet.png",
        "test": "../bitmaps_test/20190920-112812/prod_test___0_document_1_tablet.png",
        "selector": "document",
        "fileName": "prod_test___0_document_1_tablet.png",
        "label": "/",
        "misMatchThreshold": 0.1,
        "url": "http://local.creighton.com//",
        "referenceUrl": "https://alliance.creighton.edu/",
        "expect": 0,
        "viewportLabel": "tablet",
        "diff": {
          "isSameDimensions": false,
          "dimensionDifference": {
            "width": 0,
            "height": -35
          },
          "misMatchPercentage": "38.75",
          "analysisTime": 155
        },
        "diffImage": "../bitmaps_test/20190920-112812/failed_diff_prod_test___0_document_1_tablet.png"
      },
      "status": "fail"
    },
    {
      "pair": {
        "reference": "../bitmaps_reference/prod_test___0_document_2_desktop_display.png",
        "test": "../bitmaps_test/20190920-112812/prod_test___0_document_2_desktop_display.png",
        "selector": "document",
        "fileName": "prod_test___0_document_2_desktop_display.png",
        "label": "/",
        "misMatchThreshold": 0.1,
        "url": "http://local.creighton.com//",
        "referenceUrl": "https://alliance.creighton.edu/",
        "expect": 0,
        "viewportLabel": "desktop display",
        "diff": {
          "isSameDimensions": false,
          "dimensionDifference": {
            "width": 0,
            "height": 16
          },
          "misMatchPercentage": "42.39",
          "analysisTime": 226
        },
        "diffImage": "../bitmaps_test/20190920-112812/failed_diff_prod_test___0_document_2_desktop_display.png"
      },
      "status": "fail"
    }
  ],
  "id": "prod_test"
});