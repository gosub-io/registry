# Gosub Registry

A simple repository with some files is enough to form a registry.

Cargo assumes that a registry is a git repository.

You should add it to your .cargo/config.toml:

```
[registries]
gosub ={ index = "https://github.com/gosub-io/registry.git" }
```



Main file in root:	config.json

This contains:

```
{
    "dl": "https://crates.io/api/v1/crates",
    "api": "https://crates.io"
}
```

If you do not have an API, you can remove it. It's possible to do auth with `auth-required` as well.

The dl has a few keywords which you can use:

{crate}				name of the crate
{version} 			crate version
{prefix}			prefix (go/su for gosub)
{lowerprefix}		lowercase prefix
{sha256-checksum}	sha256 of the crate

If no keywords are found, cargo uses: `/{crate}/{version}/download`


The download endpoint should return the .crate file. 

So, suppose we have a registry with the following config.json:

```
{
  "dl": "https://registry.gosub.io/{crate}-{version}.crate"
}
```

When we install the gosub-engine v0.1.0 package, we download the crate from:

```
https://registry.gosub.io/gosub-engine-v0.1.0.crate
```



## Index files
Crate information can be found in the index file and is located like:

/config.json
/go/
/go/su/
/go/su/gosub-engine

Each line in this file is a version of the given crate. It's a single line json blob. Note that it MUST be a single line per version.

Note that creating this json data is a bit tricky, as there is no direct way to do it in a single step. However, there is a create-index.sh file in this repository that you can use (YMMV).
