# How to contribute

Here are some guidelines to help you to contribute efficiently to the project.

## Issues

Feel free to [submit an issue](https://github.com/monsieurluge/result/issues) if you found something wrong, if there is a mistake in the files or if you need a new feature.

## Merge requests

In addition to the issues don't hesitate to create a merge request (fork the project, then submit your merge request). It helps to target your need and to communicate.

Don't forget to follow the code rules defined in the [PHP Mess Detector file](./phpmd.xml) and to update the tests if needed ; a merge request which did not match these constraints will be automatically rejected.

## Constraints

### Static analysis

The project follows some PHP Mess Detector rules (PHPMD) which must be met on each update. Some of them have been tweaked as described below.

All the rules can be found on the PHPMD website at [phpmd.org](https://phpmd.org/).

Here are all the applied rules:
 - rulesets/unusedcode.xml
 - rulesets/cleancode.xml
 - rulesets/controversial.xml
 - rulesets/design.xml
 - rulesets/naming.xml
 - rulesets/codesize.xml

Here are the tweaks:
 - the classes MUST NOT exceed 300 lines of code, line jumps excluded
 - the methods MUST NOT exceed 50 lines of code, line jumps excluded
 - the methods MUST NOT accept more than 5 parameters
 - the public methods count MUST NOT exceed 10

### PSR

The following recommandations are in place:

 - PSR1 _basic coding standard_
 - PSR4 _autoloading standard_
 - PSR12 _coding style guide_

### Tests

The existing test suite must pass on each project update. The tests must be updated if needed.

Each new behaviour or object must be covered by a test suite.
