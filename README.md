# infolio

A versatile portifolio website created specifically for electronics projects.

## Project Library Management

To keep things as simple as possible the project library uses a simple file structure:

  - `projects/`
      - `project_name/` Append .ignore to ignore the project.
	      - `images/`
		      - `main/` All the stuff that will go in the main carousel.
		      - `board/` PCB images.
			  - `schematic/` Schematic images.
			  - `misc/` Miscellaneous images that will be added in the description.
          - `description[.lang].html` Description of the project.
		  - `project[.lang].json` Project description file.

### Project Definition

The `project.json` definition file is a very simple file too, here's an example one with every option you can have:

    {
    	"name": "Example",
    	"category": "Examples",
    	"brief": "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.",
    	"highlights": {
    	    "github": "hello/world",
    	    "tindie": "12345"
    	},
    	"links" : [
    	    {
    	        "title": "Project Website",
    	        "url": "http://example.com/"
    	    },
    	]
    }


