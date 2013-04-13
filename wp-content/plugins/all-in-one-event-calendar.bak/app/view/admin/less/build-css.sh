#!/bin/bash

LESSC="lessc --no-color --yui-compress --include-path=."

if which -s lessc; then
	$LESSC timely-bootstrap.less > ../css/bootstrap.min.css
else
  echo 'Error: lessc not found. Install Node.js then: npm install -g less';
	exit 1;
fi
