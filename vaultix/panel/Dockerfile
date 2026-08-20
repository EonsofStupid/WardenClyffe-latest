FROM golang:1.26-alpine AS build
WORKDIR /src
COPY go.mod go.sum ./
RUN go mod download
COPY . .
RUN CGO_ENABLED=0 go build -trimpath -o /out/vaultix-panel .

FROM alpine:3.20
RUN adduser -D -H panel
USER panel
COPY --from=build /out/vaultix-panel /usr/local/bin/vaultix-panel
ENTRYPOINT ["vaultix-panel"]
