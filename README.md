# Item Similarity: content-based, schema-less recommendation service

A simple recommendation service which computes the similarity of items. Since
this is part of my MSc project, I am currently spending more time on getting the
thesis right, I will enrich the documentation here.

## Concept

### Similarity Computation

The similarity between two items is computed as follows:

Given the following two JSON documents:

```json
a = {
    "brand": "Addi",
    "model": "Speedy",
    "colors": ["black", "white"],
    "category": "Shoes",
    "size": 42
}
b = {
    "brand": "Prima",
    "model": "Kazak",
    "colors": ["red", "white"],
    "category": "Sweater",
    "sleeves": "long"
}
```

First, any item features which are not in both documents are discared:

```json
a = {
    "brand": "Addi",
    "model": "Speedy",
    "colors": "black,white",
    "category": "Shoes",
}
b = {
    "brand": "Prima",
    "model": "Kazak",
    "colors": "red,white",
    "category": "Sweater",
}
```

Second, the documents are converted into lists with the keys as a prefix to the values:

```json
a = ["brand_Addi", "model_Ayak", "colors_black", "colors_white", "category_Shoes"]
b = ["brand_Addi", "model_Kazak", "colors_red", "colors_white", "category_Sweater"]
```

Finally, the variant of the tanimoto coefficient is calculated:

```
nA = number of features in A
nB = number of features in B
nAB = number of intersecting features
score = nAB / (nA + nB - nAB)
```

### Similarity index

The index is kept in a MongoDB collection with a document for each feature. This
document also keeps track of its similarity score against other documents. Every time
a new record is processed, the similarity to other documents is computed and stored.
This score is then added to the other document as well. Thus when a similarity
score is requested for a document, the end result is already pre-computed.

### API

The index is managed by POST and DELETE requests. The score is fetched via GET.

The route prefix **{index}** allows maintaining more than one index within an instance.

**POST /{index}** Posts a document to the index and calculates the similarity score

**DELETE /{index}** Deletes a document

**GET /{index}?itemIds=1,2,3** Returns similar items for the items in the GET parameter.

## Installation

```bash
$ git clone https://github.com/halk/item-similarity
$ cd item-similarity
$ cp config/config.php.dist config/config.php
```

Please see the MSc project repository for provisioning.

## Tests

```bash
$ cp phpunit.xml.dist phpunit.xml
$ phpunit
```
