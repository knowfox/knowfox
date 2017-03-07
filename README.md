# Knowfox

Knowfox is my Personal Knowledge Management system. Having been an keen Evernote user since 2012, I finally got around to taking my precious notes to my own server.

## I want

* Hierarchies. Inspired by Dave Winer's Worldoutline and Fargo, I want my knowledge base to have a deep structure, not be only two stories tall.
* Tags. Where a single hierarchy is not enough, tags can link common topics in the least intrusive way. Since the times of Web 2.0, tags are everywhere.
* Typed relationships. Sometimes though, hierarchies are too strict and tags are too loose. For example, I want to link authors to books, founders to companies, cause to effect. For this, typed, bi-directional relationships are king.
* Markdown. There are other many similar and nice text formats, but Markdown is simple and popular and has won the race.
* Bookmarking. I frequently take note of websites and like to mark them for later reading.
* Pictures. My notes have lots of pictures. Mostly screenshots, but some photos or diagrams added as well.
* Privacy. All my notes and pictures are mine and should be visible to no one.
* Simple journalling. I used Evernote on a daily basis and will do so with Knowfox, too. Easy, date-based journalling is a feature I use every day.
* Sharing. Sometimes I want to share a note and its pictures. This should be painless and explicit.
* Publishing. Knowledge wants to be communicated sometimes. For this, I want to export a sub-tree of my hierarchy as beautiful slide deck, effortlessly.

This gives me the basic structure. On top of this,

* all my Evernote notes should go in there,
* my catalogue of eBooks as well,
* my timelines, too.

The resulting system should be easy to understand, maintain and deploy. CouchDB would have been an nice option with Hoodie on to of it. Ultimately though, I felt more confident with *Laravel 5 and MySQL* so this is what Knowfox is built on.

## Status

Knowfox is usable and very nicely so.

* Full text search works, maybe even better than the one in Evernote.
* The integrated Markdown editor has no inline preview of images, but otherwise is a joy to use.
* Picture handling is very slick, thanks to the integrated Dropzone.js and automatic inclusion of image links into the note's Markdown.
* A bookmarklet helps me to bookmark websites and gather their content for later reference.
* I have imported my most important Evernote notebooks and now rely on it for my everyday work and projects.
* There is a hosted version at https://knowfox.com which is free to use. However, I make no guarantees as to the stability of this service. Ultimately, Knowfox is meant for self hosting.

There is a [brief presentation](https://knowfox.com/presentation/47d6c8de/013c/11e7/8a8c/56847afe9799/index.html) about the system, too.

## The Future

I have built Knowfox in a frency to have a usable system as soon as possible. For others to be able to contribute, it most urgently needs automated tests. Some more importers for my other databases for eBooks and timelines will follow. Maybe native apps or a FUSE filesystem at some point.