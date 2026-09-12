#!/bin/sh
# [genz-guard] enable repo-local git hooks (run once after clone)
git config core.hooksPath githooks
echo "[genz-guard] hooks enabled: $(git config core.hooksPath)"
