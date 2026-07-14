package main

import (
	"encoding/json"
	"math"
	"os"
)

type Point struct {
	ID   int     `json:"id"`
	Name string  `json:"name"`
	X    float64 `json:"x"`
	Y    float64 `json:"y"`
}

type Step struct {
	From     int     `json:"from"`
	To       int     `json:"to"`
	Distance float64 `json:"distance"`
	Searched []int   `json:"searched"`
}

type Result struct {
	Route    []Point `json:"route"`
	Steps    []Step  `json:"steps"`
	Distance float64 `json:"distance"`
}

func dist(a, b Point) float64 {
	dx := a.X - b.X
	dy := a.Y - b.Y
	return math.Sqrt(dx*dx + dy*dy)
}

func buildMatrix(pts []Point) [][]float64 {
	n := len(pts)
	M := make([][]float64, n)
	for i := range M {
		M[i] = make([]float64, n)
		for j := range M[i] {
			M[i][j] = dist(pts[i], pts[j])
		}
	}
	return M
}

func nearestNeighbor(pts []Point, M [][]float64) ([]int, []Step) {
	n       := len(pts)
	visited := make([]bool, n)
	order   := make([]int, 0, n+1)
	steps   := make([]Step, 0, n)

	cur := 0
	visited[0] = true
	order = append(order, 0)

	for s := 1; s < n; s++ {
		best     := -1
		bestDist := math.MaxFloat64
		searched := []int{}

		for j := 0; j < n; j++ {
			if !visited[j] {
				searched = append(searched, j)
				if M[cur][j] < bestDist {
					bestDist = M[cur][j]
					best     = j
				}
			}
		}

		steps    = append(steps, Step{From: cur, To: best, Distance: bestDist, Searched: searched})
		visited[best] = true
		order    = append(order, best)
		cur      = best
	}

	// close circuit
	steps = append(steps, Step{From: cur, To: 0, Distance: M[cur][0], Searched: []int{}})
	order = append(order, 0)

	return order, steps
}

func twoOpt(order []int, M [][]float64) []int {
	route    := make([]int, len(order)-1)
	copy(route, order[:len(order)-1])
	n        := len(route)
	improved := true

	for improved {
		improved = false
		for i := 0; i < n-1; i++ {
			for j := i + 2; j < n; j++ {
				if i == 0 && j == n-1 {
					continue
				}
				a, b := route[i], route[i+1]
				c, d := route[j], route[(j+1)%n]

				if M[a][c]+M[b][d] < M[a][b]+M[c][d]-1e-10 {
					lo, hi := i+1, j
					for lo < hi {
						route[lo], route[hi] = route[hi], route[lo]
						lo++
						hi--
					}
					improved = true
				}
			}
		}
	}

	route = append(route, route[0])
	return route
}

func main() {
	var pts []Point
	if err := json.NewDecoder(os.Stdin).Decode(&pts); err != nil {
		os.Exit(1)
	}

	if len(pts) < 3 {
		os.Exit(1)
	}

	M            := buildMatrix(pts)
	nnOrder, steps := nearestNeighbor(pts, M)
	optOrder     := twoOpt(nnOrder, M)

	// build route slice from optOrder
	route := make([]Point, len(optOrder))
	for i, idx := range optOrder {
		route[i] = pts[idx]
	}

	total := 0.0
	for i := 1; i < len(route); i++ {
		total += dist(route[i-1], route[i])
	}

	result := Result{
		Route:    route,
		Steps:    steps,
		Distance: total,
	}

	json.NewEncoder(os.Stdout).Encode(result)
}
