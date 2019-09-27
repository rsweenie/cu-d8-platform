#!/bin/bash
# Imports config for all sites on a given environment
# Not automated, intended to be run locally from within the VM 

# takes environment as an argument
# $1 = environment

Steps: 
1. finds all sites somewhere, possibly in this g directory
2. loops through the sites
3. gets the site uri
4. runs the config import 