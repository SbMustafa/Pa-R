FROM golang:1.25 AS build
WORKDIR /app
ENV GOFLAGS=-mod=mod
COPY . .
RUN go mod tidy && go build -o /api-server .

FROM debian:bookworm-slim
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/*
COPY --from=build /api-server /api-server
EXPOSE 8080
ENTRYPOINT ["/api-server"]
