# Changelog

## [1.8](https://github.com/moodlepeers/moodle-mod_groupformation/tree/HEAD) (2021-04-01)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/1.7...HEAD)

**Closed issues:**

- Undefined constant COURSE\_MODULE [\#87](https://github.com/moodlepeers/moodle-mod_groupformation/issues/87)
- Missing table in privacy provider and Moodle 3.9 incompatibility [\#85](https://github.com/moodlepeers/moodle-mod_groupformation/issues/85)
- Core privacy tests are failing becuse of a typo in table name [\#83](https://github.com/moodlepeers/moodle-mod_groupformation/issues/83)
- Critical Bug for Moodle 3.8: Installation of mod\_groupformation causes loss of functionality in Moodle 3.8 [\#79](https://github.com/moodlepeers/moodle-mod_groupformation/issues/79)

**Merged pull requests:**

- Feature participant list [\#89](https://github.com/moodlepeers/moodle-mod_groupformation/pull/89) ([stefanjung](https://github.com/stefanjung))
- Fix constant name in privacy provider class [\#88](https://github.com/moodlepeers/moodle-mod_groupformation/pull/88) ([golenkovm](https://github.com/golenkovm))
- Fix privacy provider to be Moodle 3.9 compatible [\#86](https://github.com/moodlepeers/moodle-mod_groupformation/pull/86) ([golenkovm](https://github.com/golenkovm))
- Fix wrong name for prefix\_groupformation\_answers table [\#84](https://github.com/moodlepeers/moodle-mod_groupformation/pull/84) ([golenkovm](https://github.com/golenkovm))

## [1.7](https://github.com/moodlepeers/moodle-mod_groupformation/tree/1.7) (2020-08-24)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.6.2...1.7)

**Implemented enhancements:**

- Info-text and notification \(opt-in\) text needs to be changed/updated, to inform students about use of the data for scientific analysis etc. [\#71](https://github.com/moodlepeers/moodle-mod_groupformation/issues/71)
- EU Datenschutzverordnung: Download aller über "mich" gespeicherten Daten \(aus allen Kursen\) anbieten [\#63](https://github.com/moodlepeers/moodle-mod_groupformation/issues/63)
- Student view.php: use settings for archiving days in text instead of fixed 360 [\#44](https://github.com/moodlepeers/moodle-mod_groupformation/issues/44)

**Fixed bugs:**

- \(cron\) partisipant\_parser.php:85 Clone on NULL object causes exception [\#70](https://github.com/moodlepeers/moodle-mod_groupformation/issues/70)
- phpunittest error: tool\_dataprivacy\_expired\_contexts\_testcase::test\_expired\_course\_related\_contexts Unexpected debugging\(\) call detected. [\#67](https://github.com/moodlepeers/moodle-mod_groupformation/issues/67)

**Closed issues:**

- End date for answers [\#80](https://github.com/moodlepeers/moodle-mod_groupformation/issues/80)
- externallib [\#66](https://github.com/moodlepeers/moodle-mod_groupformation/issues/66)
- The english language string in questionnaire\_availability\_info\_until | groupformation is in German [\#65](https://github.com/moodlepeers/moodle-mod_groupformation/issues/65)

**Merged pull requests:**

- Feature one of bin [\#82](https://github.com/moodlepeers/moodle-mod_groupformation/pull/82) ([stefanjung](https://github.com/stefanjung))
- One of bin criterion [\#73](https://github.com/moodlepeers/moodle-mod_groupformation/pull/73) ([stefanjung](https://github.com/stefanjung))
- English string variable in English [\#64](https://github.com/moodlepeers/moodle-mod_groupformation/pull/64) ([germanvaleroelizondo](https://github.com/germanvaleroelizondo))

## [v1.6.2](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.6.2) (2018-05-11)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.6.1...v1.6.2)

## [v1.6.1](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.6.1) (2018-05-11)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.6...v1.6.1)

## [v1.6](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.6) (2018-05-10)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.5.1...v1.6)

**Implemented enhancements:**

- "my group" does not update list of users due to leaves and manual changes [\#61](https://github.com/moodlepeers/moodle-mod_groupformation/issues/61)
- While grouping is running: better instruct user by text \(reload of page after 2-5min\) [\#60](https://github.com/moodlepeers/moodle-mod_groupformation/issues/60)
- Allow to dublicate a configured groupformation activity in Moodle LMS [\#58](https://github.com/moodlepeers/moodle-mod_groupformation/issues/58)

**Merged pull requests:**

- Adding debug options for debug user [\#56](https://github.com/moodlepeers/moodle-mod_groupformation/pull/56) ([Nullmann](https://github.com/Nullmann))

## [v1.5.1](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.5.1) (2018-01-20)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.5...v1.5.1)

**Implemented enhancements:**

- show topic-offering ONLY when selecting "presentation groups" and DON'T offer topics for the other two scenarios [\#43](https://github.com/moodlepeers/moodle-mod_groupformation/issues/43)
- PHP notices in the cron output [\#28](https://github.com/moodlepeers/moodle-mod_groupformation/issues/28)
- Problem when both topics and knowledge questions are defined [\#27](https://github.com/moodlepeers/moodle-mod_groupformation/issues/27)

**Fixed bugs:**

- Type "binary" for DB-field onlyactivestudents in table groupformation causes bug with PostgreSQL [\#55](https://github.com/moodlepeers/moodle-mod_groupformation/issues/55)
- Blank screen when student access questionnaire [\#46](https://github.com/moodlepeers/moodle-mod_groupformation/issues/46)

## [v1.5](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.5) (2017-10-06)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.4...v1.5)

**Implemented enhancements:**

- Accessing URL hacks via links [\#18](https://github.com/moodlepeers/moodle-mod_groupformation/issues/18)
- Visualization of the sorted topics [\#16](https://github.com/moodlepeers/moodle-mod_groupformation/issues/16)

**Closed issues:**

- Unusable slow: grouping\_view.php?id=XXX&do\_show=grouping  [\#17](https://github.com/moodlepeers/moodle-mod_groupformation/issues/17)

## [v1.4](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.4) (2017-07-12)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.3.1...v1.4)

**Closed issues:**

- Translation Notification preferences [\#53](https://github.com/moodlepeers/moodle-mod_groupformation/issues/53)

## [v1.3.1](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.3.1) (2017-05-26)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.3...v1.3.1)

**Implemented enhancements:**

- Takeover of group formation to Moodle groups is slow in large university environments [\#49](https://github.com/moodlepeers/moodle-mod_groupformation/issues/49)
- \(anonymous\) how many students selected each topic [\#22](https://github.com/moodlepeers/moodle-mod_groupformation/issues/22)

**Closed issues:**

- Change message for disabled "notify teacher" function  [\#51](https://github.com/moodlepeers/moodle-mod_groupformation/issues/51)
- Template variable is not replaced [\#50](https://github.com/moodlepeers/moodle-mod_groupformation/issues/50)

## [v1.3](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.3) (2017-03-01)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/1.2.1-b...v1.3)

**Implemented enhancements:**

- Upload a version compatible with Moodle v3.2 [\#37](https://github.com/moodlepeers/moodle-mod_groupformation/issues/37)
- Reset final choices in administrator view [\#20](https://github.com/moodlepeers/moodle-mod_groupformation/issues/20)

**Fixed bugs:**

- Questionaire part character-3 appears in German always \(missing EN texts\) [\#36](https://github.com/moodlepeers/moodle-mod_groupformation/issues/36)
- layout issues with clean theme or beuth03 theme [\#35](https://github.com/moodlepeers/moodle-mod_groupformation/issues/35)
- DB error after answering character questions \(only current master, not release version\) [\#34](https://github.com/moodlepeers/moodle-mod_groupformation/issues/34)
- 7 invalid/missing string identifiers for english \(EN\) [\#33](https://github.com/moodlepeers/moodle-mod_groupformation/issues/33)
- Inconsistent handling of HTML tags in topic and knowledge [\#24](https://github.com/moodlepeers/moodle-mod_groupformation/issues/24)

**Closed issues:**

- Design differences in evaluation\_view.php for Moodle 3.1 and 3.2 [\#42](https://github.com/moodlepeers/moodle-mod_groupformation/issues/42)
- Selected value on slider not visible [\#31](https://github.com/moodlepeers/moodle-mod_groupformation/issues/31)
- Some en\_\*.xml questions still in German [\#30](https://github.com/moodlepeers/moodle-mod_groupformation/issues/30)
- Unknown string in the cron output [\#29](https://github.com/moodlepeers/moodle-mod_groupformation/issues/29)
- Text hard to read on buttons [\#26](https://github.com/moodlepeers/moodle-mod_groupformation/issues/26)
- Typo in the plugin description  [\#23](https://github.com/moodlepeers/moodle-mod_groupformation/issues/23)
- Wrong ordering? [\#21](https://github.com/moodlepeers/moodle-mod_groupformation/issues/21)
- Being able to extend the time after a student has submitted [\#19](https://github.com/moodlepeers/moodle-mod_groupformation/issues/19)
- Can not change the description of the activity [\#15](https://github.com/moodlepeers/moodle-mod_groupformation/issues/15)
- Lang-strings: sourceforge -\> github [\#14](https://github.com/moodlepeers/moodle-mod_groupformation/issues/14)

## [1.2.1-b](https://github.com/moodlepeers/moodle-mod_groupformation/tree/1.2.1-b) (2016-10-11)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/1.2-b...1.2.1-b)

## [1.2-b](https://github.com/moodlepeers/moodle-mod_groupformation/tree/1.2-b) (2016-10-11)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.1-b...1.2-b)

**Implemented enhancements:**

- Randomize order of questionaire items [\#12](https://github.com/moodlepeers/moodle-mod_groupformation/issues/12)
- Label number of students always shows current members in course, not number of students matched [\#10](https://github.com/moodlepeers/moodle-mod_groupformation/issues/10)
- Click on tab "evaluation view" takes a lot of time \(on 450 users\) [\#8](https://github.com/moodlepeers/moodle-mod_groupformation/issues/8)
- Language labels for user analysis view are in wrong language [\#7](https://github.com/moodlepeers/moodle-mod_groupformation/issues/7)

**Closed issues:**

- DB exception for students answering the questionaire  [\#13](https://github.com/moodlepeers/moodle-mod_groupformation/issues/13)
- Max. Gruppenzahl: Beschränkung in mod\_form.php aufheben [\#11](https://github.com/moodlepeers/moodle-mod_groupformation/issues/11)

## [v1.1-b](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.1-b) (2016-09-10)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.0.1b...v1.1-b)

**Fixed bugs:**

- resulting in more than 99 groups leads to error/die of groupformation [\#9](https://github.com/moodlepeers/moodle-mod_groupformation/issues/9)
- Division by zero in test\_user-generator [\#6](https://github.com/moodlepeers/moodle-mod_groupformation/issues/6)
- PHP 5.5 Abhängigkeit wegen self::class entfernen! [\#5](https://github.com/moodlepeers/moodle-mod_groupformation/issues/5)

**Closed issues:**

- Postgresql COUNT Error in SQL statement on creation of new activity instance [\#4](https://github.com/moodlepeers/moodle-mod_groupformation/issues/4)
- Requires non-standard libraries [\#3](https://github.com/moodlepeers/moodle-mod_groupformation/issues/3)
- hand-rolled forms should implement sesskey checks [\#2](https://github.com/moodlepeers/moodle-mod_groupformation/issues/2)
- direct access to $\_GET/$\_POST variables is not allowed [\#1](https://github.com/moodlepeers/moodle-mod_groupformation/issues/1)

## [v1.0.1b](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.0.1b) (2015-11-18)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v1.0...v1.0.1b)

## [v1.0](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v1.0) (2015-08-19)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v0.9.1...v1.0)

## [v0.9.1](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v0.9.1) (2015-08-17)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/v0.9...v0.9.1)

## [v0.9](https://github.com/moodlepeers/moodle-mod_groupformation/tree/v0.9) (2015-08-15)

[Full Changelog](https://github.com/moodlepeers/moodle-mod_groupformation/compare/5676d7f0b06308e3aa580d35d35ca3df677e3ccd...v0.9)



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
