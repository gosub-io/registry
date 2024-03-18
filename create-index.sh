#!/usr/bin/env bash

#
# This will generate a new index entry for the first package in the current directory
#

METADATA=$(cargo metadata --no-deps --format-version 1)

PKG_NAME=`echo $METADATA | jq '.packages[0].name' --raw-output`
PKG_VERSION=`echo $METADATA | jq '.packages[0].version' --raw-output`

# This assumes we already have build the package with "cargo package", and that this is located in ./target/package directory
CHECKSUM=$(sha256sum target/package/$PKG_NAME-$PKG_VERSION.crate | cut -d ' ' -f 1)

# This will the JQ format that generate the index entry based on the metadata from the package
JQ_FILTER=$(cat << EOH
  {
    name: .packages[0].name,
    vers: .packages[0].version,
    deps: [.packages[0].dependencies[] | {
        name: .name,
        req: .req,
        features: .features,
        optional: .optional,
        default_features: .default_features,
        target: .target,
        kind: .kind,
        registry: .registry,
        package: .package
    }],
    cksum: "$CHECKSUM",
    yanked: false,
    features: .packages[0].features,
    links: .packages[0].links,
    v: 2,
    rust_version: .packages[0].rust_version
  }
EOH
)

# Dump as a single one liner
echo $METADATA | jq "$JQ_FILTER" -c -M
