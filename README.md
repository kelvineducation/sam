# Deliverer
Manage and serve versioned static assets

## API Reference

## Creating a package

```
curl -k -sS -d '{"name": "popup"}' -H 'Content-Type: application/json' \
    https://deliverer.b.com/packages
```

## Creating a version

```
(rm -f popup.zip && cd dist && zip -r ../popup.zip .) && \
    jq -n \
        --rawfile BASE64 <(base64 popup.zip) \
        --arg HASH $(sha256sum popup.zip | awk '{print $1}') \
        '{"name": "1.1.3", "archive_hash": $HASH, "archive_base64": $BASE64}' \
        | curl -k -sS -d @- -H 'Content-Type: application/json' \
            https://deliverer.b.com/packages/popup/versions
```
